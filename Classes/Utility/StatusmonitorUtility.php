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

use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extensionmanager\Utility\ListUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
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

    public function __construct(
        private readonly ExtensionConfiguration $extensionConfiguration,
        private readonly ListUtility $listUtility,
        private readonly RequestFactory $requestFactory)
    {
    }

    /**
     * @throws \Exception
     * @return boolean
     */
    public function run()
    {
        $extConf = $this->extensionConfiguration->get('form4_statusmonitor');
        $this->pass = $extConf['password'];
        $this->posturl = $extConf['postUrl'];
        $this->user = $extConf['user'];

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
            $bodyData['version'] = VersionNumberUtility::getNumericTypo3Version();
            $extensions = $this->listUtility->getAvailableAndInstalledExtensionsWithAdditionalInformation();

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

            $defaultConnection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
            if($defaultConnection){
                $properties[] =[
                    'name' => 'SQL Version',
                    'value' => $defaultConnection->getServerVersion()
                ] ;
            }


            if(isset($properties) && !empty($properties)){
                $bodyData['properties'] = $properties;
            }
            //properties end

            //encode all data for json array
            $json = json_encode($bodyData);

            $requestOptions = [
                'headers' => ['application/json',
                ],
                'body' => $json,

            ];

            $response = $this->requestFactory->request($this->posturl, 'POST', $requestOptions);

            if ($response->getStatusCode() == 200) {
                return true;
            } else {
                throw new \Exception('No Response with Code 200! The following response was delivered: ' . $response->getStatusCode());
            }
        }

        return false;
    }
}
