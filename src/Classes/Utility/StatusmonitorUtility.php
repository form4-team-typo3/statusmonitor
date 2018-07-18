<?php
namespace FORM4\Statusmonitor\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Utility\ListUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class StatusmonitorUtility
{

    // Main Method for the Scheduler Task
    public function run()
    {
        $result = false;
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\Object\ObjectManager::class);
        
        /** @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility $configurationUtility */
        $configurationUtility = $objectManager->get(\TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility::class);
        $extConf = $configurationUtility->getCurrentConfiguration('form4_statusmonitor');
        
        if (isset($extConf['postUrl']['value']) && ! empty($extConf['postUrl']['value']) &&
             filter_var($extConf['postUrl']['value'], FILTER_VALIDATE_URL)) {
            
            $bodyArr = [];
            
            $postUrl = $extConf['postUrl']['value'];
            
            // username
            if (! empty($extConf['user']['value'])) {
                $bodyArr['id'] = trim($extConf['user']['value']);
            }
            
            if (! empty($extConf['password']['value'])) {
                $bodyArr['password'] = trim($extConf['password']['value']);
            }
            
            $bodyArr['version'] = TYPO3_version;
            
            // add extensions
            $modulesToAdd = [];
            
            /** @var \TYPO3\CMS\Extensionmanager\Utility\ListUtility $listUtility */
            $listUtility = $objectManager->get(ListUtility::class);
            $extensions = $listUtility->getAvailableAndInstalledExtensionsWithAdditionalInformation();
            
            foreach ($extensions as $key => $module) {
                if ($module['type'] == 'Local' && $module['installed'] == true) {
                    $modulesToAdd[] = [
                        'name' => $key,
                        'version' => $module['version']
                    ];
                }
            }
            
            $bodyArr['modules'] = $modulesToAdd;

            // signal/Slot to extend the bodyArr
            list ($bodyArr) = $this->getSignalSlotDispatcher()->dispatch(__CLASS__, 'ModifyDataArrayBeforeJsonEncode',
                [
                    $bodyArr
                ]);
            
            $json = json_encode($bodyArr);

            $typo3Version = VersionNumberUtility::convertVersionStringToArray(VersionNumberUtility::getCurrentTypo3Version());
            
            if ($typo3Version['version_main'] == 7 && $typo3Version['version_sub'] >= 6) {
                
                /** @var \TYPO3\CMS\Core\Http\HttpRequest $request */
                $request = $objectManager->get(\TYPO3\CMS\Core\Http\HttpRequest::class);
                $request->setUrl($postUrl);
                $request->setMethod(\TYPO3\CMS\Core\Http\HttpRequest::METHOD_POST);
                $request->setHeader('Content-Type', 'application/json');
                $request->setBody($json);
                
                list ($request) = $this->getSignalSlotDispatcher()->dispatch(__CLASS__, 'ModifyHttpRequest',
                    [
                        $request
                    ]);
                
                // sending to url
                $response = $request->send();
                if ($response->getStatus() == 200) {
                    $result = true;
                } else {
                    throw new \Exception(
                        'No Response with Code 200! The following response was delivered: ' . $response->getStatus());
                }
            }
            
            if ($typo3Version['version_main'] >= 8) {
                
                /** @var \TYPO3\CMS\Core\Http\Request $request */
                $request = $objectManager->get(\TYPO3\CMS\Core\Http\Request::class, $postUrl, 'POST');

                // Request Options
                $options = [
                    'Content-Type' => 'application/json',
                    'http_errors' => false,
                    'body' => $json
                ];
                
                list ($request, $options) = $this->getSignalSlotDispatcher()->dispatch(__CLASS__,
                    'ModifyRequestAndOptions',
                    [
                        $request,
                        $options
                    ]
                );
                
                // sending to url
                $client = $this->getClient();
                
                /** @var \GuzzleHttp\Psr7\Response $response */
                $response = $client->send($request, $options);
                
                if ($response->getStatusCode() == 200) {
                    $result = true;
                } else {
                    throw new \Exception(
                        'No Response with Code 200! The following response was delivered: ' . $response->getStatusCode());
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
            $this->signalSlotDispatcher = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class)->get(
                \TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
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