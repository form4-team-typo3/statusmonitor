<?php
namespace FORM4\Statusmonitor\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Utility\ListUtility;
use TYPO3\CMS\Core\Http\HttpRequest;

class StatusmonitorUtility
{

    protected $jsonArray = [];
    
    protected $httpRequestConfig = [];
    
    protected $httpRequestMethod = HttpRequest::METHOD_POST;
    
    /*
     * Helper methods if a signal/slot changes something
     * the httpRequest Method (HttpRequest::METHOD_POST Or HttpRequest::METHOD_GET)
     */
    public function setHttpRequestMethod($httpRequestMethod)
    {
        $this->httpRequestMethod = $httpRequestMethod;
    }
    
    /*
     * Helper methods if a signal/slot changes the httpRequest Configuration
     */
    
    public function getHttpRequestConfig()
    {
        return $this->httpRequestConfig;
    }

    public function setHttpRequestConfig($httpRequestConfig)
    {
        $this->httpRequestConfig = $httpRequestConfig;
    }

    public function addToHttpRequestConfig($key, $value)
    {
        if (isset($key) & ! empty($key)) {
            $this->httpRequestConfig[$key] = $value;
        }
    }

    /*
     * Helper methods if a signal/slot changes the JSON Object Configuration
     */
    
    public function getJsonArray()
    {
        return $this->jsonArray;
    }

    public function setJsonArray($jsonArray)
    {
        $this->jsonArray = $jsonArray;
    }

    public function addToJsonArray($key, $value)
    {
        if (isset($key) & ! empty($key)) {
            $this->jsonArray[$key] = $value;
        }
    }

    
    
    //Main Method for the Scheduler Task
    public function run()
    {
        
        $result = false;
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        
        /** @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility $configurationUtility */
        $configurationUtility = $objectManager->get(\TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility::class);
        $extConf = $configurationUtility->getCurrentConfiguration('form4_statusmonitor');

        if (isset($extConf['statusmonitor.postUrl']['value']) && ! empty($extConf['statusmonitor.postUrl']['value']) && filter_var($extConf['statusmonitor.postUrl']['value'], FILTER_VALIDATE_URL)) {
            $postUrl = $extConf['statusmonitor.postUrl']['value'];
            
            // username
            if (!empty($extConf['statusmonitor.user']['value'])) {
                $this->addToJsonArray('id', trim($extConf['statusmonitor.user']['value']));
            }
            
            // password
            if (!empty($extConf['statusmonitor.password']['value'])) {
                $this->addToJsonArray('password', trim($extConf['statusmonitor.password']['value']));
            }
            
            // typo3 Version
            $this->addToJsonArray('version', TYPO3_version);
            
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
            
            $this->addToJsonArray('modules', $modulesToAdd);
            //signal/Slot to extend the jsonArray
            $this->getSignalSlotDispatcher()->dispatch(__CLASS__, 'AddToDataToArrayBeforeJsonEncode', [
                $this
            ]);

            $json = json_encode($this->getJsonArray());
            
            // sending to url
            //set the httpRequest configurations 
            $this->getSignalSlotDispatcher()->dispatch(__CLASS__, 'ChangeHttpRequestConfiguration', [
                $this
            ]);
            
            /** @var \TYPO3\CMS\Core\Http\HttpRequest $request */
            $request = $objectManager->get(HttpRequest::class);
            $request->setUrl($postUrl);
            $request->setMethod($this->httpRequestMethod);
            $request->setHeader('Content-Type','application/json');
            
            //additional Configuration will be merged with existing in httpRequest
            $request->setConfiguration($this->httpRequestConfig);
                        
            $request->setBody($json);
            
            $result = $request->send();
            
            if ($result->getStatus() == 200) {
                $result = true;
            } else {
                throw new \Exception('No Response with Code 200! The following response was delivered: ' . $result->getStatus() );
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
            $this->signalSlotDispatcher = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class)->get(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
        }
        return $this->signalSlotDispatcher;
    }
}