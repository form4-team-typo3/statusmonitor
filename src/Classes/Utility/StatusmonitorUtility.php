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

/**
 * @author Thomas Grothaus <thomas.grothaus@form4.de>
 */
class StatusmonitorUtility
{
    /**
     * @throws \Exception
     * @return boolean
     */
    public function run()
    {
        $result = false;
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        
        /** @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility $configurationUtility */
        $configurationUtility = $objectManager->get(ConfigurationUtility::class);
        $extConf = $configurationUtility->getCurrentConfiguration('form4_statusmonitor');
        
        if (
            isset($extConf['postUrl']['value']) && 
            !empty($extConf['postUrl']['value']) &&
            filter_var($extConf['postUrl']['value'], FILTER_VALIDATE_URL)
        ) {
            
            $bodyData = [];
            
            $postUrl = $extConf['postUrl']['value'];
            
            // Get credentials.
            if (! empty($extConf['user']['value'])) {
                $bodyData['id'] = trim($extConf['user']['value']);
            }
            if (! empty($extConf['password']['value'])) {
                $bodyData['password'] = trim($extConf['password']['value']);
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

            // signal/Slot to extend the bodyData
            list ($bodyData) = $this->getSignalSlotDispatcher()->dispatch(
                __CLASS__,
                'ModifyDataArrayBeforeJsonEncode',
                [
                    $bodyData
                ]
            );
            
            $json = json_encode($bodyData);

            $typo3Version = VersionNumberUtility::convertVersionStringToArray(VersionNumberUtility::getCurrentTypo3Version());
            
            // Create and send request in TYPO3 7.6.
            if ($typo3Version['version_main'] == 7 && $typo3Version['version_sub'] >= 6) {
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
            if ($typo3Version['version_main'] >= 8) {
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
    protected function getClient(): \GuzzleHttp\ClientInterface
    {
        $httpOptions = $GLOBALS['TYPO3_CONF_VARS']['HTTP'];
        $httpOptions['verify'] = filter_var($httpOptions['verify'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $httpOptions['verify'];
        
        return GeneralUtility::makeInstance(\GuzzleHttp\Client::class, $httpOptions);
    }
}