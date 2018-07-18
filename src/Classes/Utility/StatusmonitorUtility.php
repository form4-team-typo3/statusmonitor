<?php
namespace FORM4\Statusmonitor\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Utility\ListUtility;

class StatusmonitorUtility
{

    protected $jsonArray = [];

    //request 7.6
    protected $httpRequestConfig = [];
    protected $httpRequestMethod = '';
    
    //request 8.7 or higher
    protected $requestFactoryOptions = [];
    protected $requestFactoryMethod = 'POST';
    
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
     * Helper methods for version > 8.7 requestFactory
     */

    public function setRequestFactoryMethod($method)
    {
        $this->requestFactoryMethod = $method;
    }
    
    public function setRequestFactoryOptions($options){
        $this->requestFactoryOptions = $options;
    }
    
    public function getRequestFactoryOptions(){
        return $this->requestFactoryOptions;
    }
    
    public function addToRequestFactoryOptions($key, $value){
        if (isset($key) & ! empty($key)) {
            $this->requestFactoryOptions[$key] = $value;
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

        if (isset($extConf['postUrl']['value']) && ! empty($extConf['postUrl']['value']) && filter_var($extConf['postUrl']['value'], FILTER_VALIDATE_URL)) {
            //set default Method
            
            if(class_exists(\TYPO3\CMS\Core\Http\HttpRequest::class)){
                $this->httpRequestMethod = \TYPO3\CMS\Core\Http\HttpRequest::METHOD_POST;
            }
            
            $postUrl = $extConf['postUrl']['value'];
            // username
            if (!empty($extConf['user']['value'])) {
                $this->addToJsonArray('id', trim($extConf['user']['value']));
            }
            
            // password
            if (!empty($extConf['password']['value'])) {
                $this->addToJsonArray('password', trim($extConf['password']['value']));
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

            $typo3Version = explode('.', TYPO3_branch);
          
            
            if($typo3Version[0] ==  7 && $typo3Version[1] >=  6){
                
                // sending to url
                //set the httpRequest configurations 
                $this->getSignalSlotDispatcher()->dispatch(__CLASS__, 'ChangeHttpRequestConfiguration', [
                    $this
                ]);

                /** @var \TYPO3\CMS\Core\Http\HttpRequest $request */
                $request = $objectManager->get(\TYPO3\CMS\Core\Http\HttpRequest::class);
                $request->setUrl($postUrl);
                $request->setMethod($this->httpRequestMethod);
                $request->setHeader('Content-Type','application/json');
                
                //additional Configuration will be merged with existing in httpRequest
                $request->setConfiguration($this->httpRequestConfig);
                $request->setBody($json);
                
                $response = $request->send();
                if ($response->getStatus() == 200) {
                    $result = true;
                } else {
                    throw new \Exception('No Response with Code 200! The following response was delivered: ' . $response->getStatus() );
                }
            }
            
            if($typo3Version[0] >=  8){
                
                //Request Options
                $this->addToRequestFactoryOptions(
                    'headers',[
                        'Content-Type' => 'application/json'
                    ]
                );
                
                $this->addToRequestFactoryOptions('body', $json); 
                $this->addToRequestFactoryOptions('http_errors', false); 
                
                // sending to url
                //set the requestFactory configurations 
                $this->getSignalSlotDispatcher()->dispatch(__CLASS__, 'ChangeRequestFactoryOptions', [
                    $this
                ]);
                
                /** @var \TYPO3\CMS\Core\Http\RequestFactory $request */
                $request = $objectManager->get(\TYPO3\CMS\Core\Http\RequestFactory::class);
                
                /** @var \GuzzleHttp\Psr7\Response $response */
                $response = $request->request($postUrl, $this->requestFactoryMethod, $this->getRequestFactoryOptions());

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
            $this->signalSlotDispatcher = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class)->get(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
        }
        return $this->signalSlotDispatcher;
    }
    
}