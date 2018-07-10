<?php
namespace FORM4\Statusmonitor\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Utility\ListUtility;

class StatusmonitorUtility
{

    protected $postOptions = [
        'https' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json" . PHP_EOL . "Accept: application/json" . PHP_EOL
        ]
    ];

    public function run($password, $username, $postUrl)
    {
        $result = false;
        if (isset($postUrl) && ! empty($postUrl) && filter_var($postUrl, FILTER_VALIDATE_URL)) {
            
            // username
            if (isset($username) && ! empty($username)) {
                $jsonArray['id'] = trim($username);
            }
            
            // password
            if (isset($password) && ! empty($password)) {
                $jsonArray['password'] = trim($password);
            }
            
            // typo3 Version
            $jsonArray['version'] = TYPO3_version;
            
            // extensions
            $jsonArray['modules'] = [];
            
            /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
            $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                \TYPO3\CMS\Extbase\Object\ObjectManager::class);
            
            /** @var \TYPO3\CMS\Extensionmanager\Utility\ListUtility $listUtility */
            $listUtility = $objectManager->get(ListUtility::class);
            $modules = $listUtility->getAvailableAndInstalledExtensionsWithAdditionalInformation();
            
            foreach ($modules as $module) {
                if ($module['type'] == 'Local' && $module['installed'] == true) {
                    $jsonArray['modules'][] = [
                        'name' => $module['title'],
                        'version' => $module['version']
                    
                    ];
                }
            }
            
            // MAYBE: signal/Slot to extend the jsonArray
            
            $json = json_encode($jsonArray);
            
            // sending to url
            $result = $this->sendWithCurl($json, $postUrl);
        }
        return $result;
    }

    protected function sendWithCurl($jsonContent, $url)
    {
        $result = false;
        // Initiate cURL.
        $ch = curl_init();
        // define options
        $optArray = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonContent,
            CURLOPT_HTTPHEADER,
            [
                'Content-Type: application/json'
            ]
        
        );
        // apply options
        curl_setopt_array($ch, $optArray);
        
        // Execute the request
        $curlresult = curl_exec($ch);
        $errors = curl_error($ch);
        $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response == 200) {
            $result = true;
        } else {
            throw new \Exception('No Response with Code 200! The following response was delivered: ' . $response);
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
}