<?php
namespace FORM4\Statusmonitor\Utility;

/*
 * Copyright notice
 *
 * (c) 2018 form4 GmbH & Co. KG <typo3@form4.de>
 * All rights reserved
 *
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Utility\ListUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * @author Thomas Grothaus <thomas.grothaus@form4.de>
 */
class StatusmonitorUtility
{
    
    /**
     * @var string
     */
    private $user;
    
    /**
     * @var string
     */
    private $pass;
    
    /**
     * @var string
     */
    private $posturl;
    
    
    
    /**
     * @throws \Exception
     * @return boolean
     */
    public function run()
    {
        $result = false;
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        
        $versionArr = VersionNumberUtility::convertVersionStringToArray(VersionNumberUtility::getNumericTypo3Version());
        if($versionArr['version_main']>8){
            /**
             * @var ExtensionConfiguration $extensionConfiguration
             */
            $extensionConfiguration = $objectManager->get(ExtensionConfiguration::class);
            $extConf = $extensionConfiguration->get('form4_statusmonitor');
            $this->pass = $extConf['password'];
            $this->posturl = $extConf['postUrl'];
            $this->user = $extConf['user'];
        }else{
            /** @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility $configurationUtility */
            $configurationUtility = $objectManager->get(ConfigurationUtility::class);
            $extConf = $configurationUtility->getCurrentConfiguration('form4_statusmonitor');
            $this->pass = $extConf['password']['value'];
            $this->posturl = $extConf['postUrl']['value'];
            $this->user = $extConf['user']['value'];
        }
        
        if (
            isset($this->posturl) && 
            !empty($this->posturl) &&
            filter_var($this->posturl, FILTER_VALIDATE_URL)
        ) {
            
            $bodyData = [];            
            $postUrl = $this->posturl;
            
            // Get credentials.
            if (! empty($this->user)) {
                $bodyData['id'] = trim($this->user);
            }
            if (! empty($this->pass)) {
                $bodyData['password'] = trim($this->pass);
            }
            
            // Get TYPO3 version.
            $bodyData['version'] = TYPO3_version;
            
            /** @var \TYPO3\CMS\Extensionmanager\Utility\ListUtility $listUtility */
            $listUtility = $objectManager->get(ListUtility::class);
            $extensions = $listUtility->getAvailableAndInstalledExtensionsWithAdditionalInformation();
            
            // Get extensions.
            $bodyData['modules'] = [];
            foreach ($extensions as $key => $module) {
                if ($module['type'] == 'Local' && $module['installed'] == true) {
                    $bodyData['modules'][] = [
                        'name' => $key,
                        'version' => $module['version']
                    ];
                }
            }

            //properties start 
            $properties = [];
            
            if(phpversion()){
                $properties[] =[
                    'name' => 'php',
                    'value' => phpversion()
                ] ;
            }else{
                $properties[] =[
                    'name' => 'php',
                    'value' => 'phpversion() not enabled'
                ] ;
            }
            
            if(function_exists('php_uname')){
                $properties[] =[
                    'name' => 'OS',
                    'value' => php_uname('s'). ' ' . php_uname('v')
                ] ;
            }else{
                $properties[] =[
                'name' => 'OS',
                'value' => 'php_uname() not enabled'
                ];
            }

            if(function_exists('php_sapi_name')){
                $properties[] =[
                    'name' => 'Server API',
                    'value' => php_sapi_name() 
                ] ;
            }else{
                $properties[] =[
                    'name' => 'Server API',
                    'value' => 'php_sapi_name() not allowed.'
                ] ;
            }

            if( isset($_SERVER['SERVER_SOFTWARE']) && !empty($_SERVER['SERVER_SOFTWARE']) ){
                $properties[] =[
                    'name' => 'Server',
                    'value' => $_SERVER['SERVER_SOFTWARE']
                ] ;
            }
            
            if ($versionArr['version_main'] == 7) {
                $res = $this->getDatabaseConnection()->getServerVersion();
                if($res){
                    $properties[] =[
                        'name' => 'SQL Version',
                        'value' => $res
                    ] ;
                }
                
            }
            
            if ($versionArr['version_main'] >= 8) {
                $defaultConnection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
                if($defaultConnection){
                    $properties[] =[
                        'name' => 'SQL Version',
                        'value' => $defaultConnection->getServerVersion()
                    ] ;
                }
                
            }
            
    
            if(isset($properties) && !empty($properties)){
                $bodyData['properties'] = $properties;
            }
            //properties end 
            
            // signal/Slot to extend the bodyData
            list ($bodyData) = $this->getSignalSlotDispatcher()->dispatch(
                __CLASS__,
                'ModifyDataArrayBeforeJsonEncode',
                [
                    $bodyData
                ]
            );
            
            //encode all data for json array
            $json = json_encode($bodyData);
            

            // Create and send request in TYPO3 7.6.
            if ($versionArr['version_main'] == 7 && $versionArr['version_sub'] >= 6) {
                // Create request.
                /** @var \TYPO3\CMS\Core\Http\HttpRequest $request */
                $request = $objectManager->get(\TYPO3\CMS\Core\Http\HttpRequest::class);
                $request->setUrl($postUrl);
                $request->setMethod(\TYPO3\CMS\Core\Http\HttpRequest::METHOD_POST);
                $request->setHeader('Content-Type', 'application/json');
                $request->setBody($json);
                
                // Modify request by hooks.
                list($request) = $this->getSignalSlotDispatcher()->dispatch(
                    __CLASS__, 
                    'ModifyHttpRequest',
                    [
                        $request
                    ]
                );
                
                // Send requests.
                $response = $request->send();
                
                if ($response->getStatus() == 200) {
                    $result = true;
                } else {
                    throw new \Exception('No Response with Code 200! The following response was delivered: ' . $response->getStatus());
                }
            }
            
            // Create and send request in TYPO3 8.
            if ($versionArr['version_main'] >= 8) {
                // Create request and options.
                /** @var \TYPO3\CMS\Core\Http\Request $request */
                $request = $objectManager->get(\TYPO3\CMS\Core\Http\Request::class, $postUrl, 'POST');
                $options = [
                    'Content-Type' => 'application/json',
                    'http_errors' => false,
                    'body' => $json
                ];
                
                // Modify request and options by hooks.
                list($request, $options) = $this->getSignalSlotDispatcher()->dispatch(
                    __CLASS__,
                    'ModifyRequestAndOptions',
                    [
                        $request,
                        $options
                    ]
                );
                
                // Send request.
                $client = $this->getClient();
                $response = $client->send($request, $options);
                
                if ($response->getStatusCode() == 200) {
                    $result = true;
                } else {
                    throw new \Exception('No Response with Code 200! The following response was delivered: ' . $response->getStatusCode());
                }
            }
        }
        return $result;
    }

    /**
     * Get the SignalSlot dispatcher
     *
     * @return \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     */
    protected function getSignalSlotDispatcher()
    {
        if (! isset($this->signalSlotDispatcher)) {
            $this->signalSlotDispatcher = GeneralUtility::makeInstance(ObjectManager::class)
                ->get(Dispatcher::class);
        }
        return $this->signalSlotDispatcher;
    }

    /**
     * Creates the client to do requests
     * 
     * @return \GuzzleHttp\ClientInterface
     */
    protected function getClient()
    {
        $httpOptions = $GLOBALS['TYPO3_CONF_VARS']['HTTP'];
        if(filter_var($httpOptions['verify'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)){
            $httpOptions['verify'] = filter_var($httpOptions['verify'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }else{
            $httpOptions['verify'];
        }   
        return GeneralUtility::makeInstance(\GuzzleHttp\Client::class, $httpOptions);
    }
    
    /**
     * Get database instance.
     * Will be initialized if it does not exist yet.
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        static $database;
        if (!is_object($database)) {
            /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $database */
            $database = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\DatabaseConnection::class);
            $database->setDatabaseUsername($GLOBALS['TYPO3_CONF_VARS']['DB']['username']);
            $database->setDatabasePassword($GLOBALS['TYPO3_CONF_VARS']['DB']['password']);
            $database->setDatabaseHost($GLOBALS['TYPO3_CONF_VARS']['DB']['host']);
            $database->setDatabasePort($GLOBALS['TYPO3_CONF_VARS']['DB']['port']);
            $database->setDatabaseSocket($GLOBALS['TYPO3_CONF_VARS']['DB']['socket']);
            $database->setDatabaseName($GLOBALS['TYPO3_CONF_VARS']['DB']['database']);
            $database->initialize();
            $database->connectDB();
        }
        return $database;
    }
}