<?php
namespace FORM4\Statusmonitor\Task;

class StatusmonitorTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask
{

    /**
     * @var string
     */
    public $statusmonitorPassword;

    /**
     * @var string
     */
    public $statusmonitorUsername;

    /**
     * @var string
     */
    public $statusmonitorPostUrl;

    /**
     * Returns the information shown in the task-list
     *
     * @return string Information-text fot the scheduler task-list
     */
    public function getAdditionalInformation()
    {
        
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        
        /** @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility $configurationUtility */
        $configurationUtility = $objectManager->get(\TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility::class);
        $extConf = $configurationUtility->getCurrentConfiguration('form4_statusmonitor');
        $lll = 'LLL:EXT:form4_statusmonitor/Resources/Private/Language/locallang_db.xlf:';
        
        if(!empty($extConf['user']['value'])){            
            $message = 'User/Id: ' . $extConf['user']['value'] . PHP_EOL;
        }

        $message .= isset($extConf['password']['value']) && ! empty($extConf['password']['value']) 
            ? $this->getLanguageService()->sL($lll . 'task.statusmonitor.passset'). PHP_EOL
            : $this->getLanguageService()->sL($lll . 'task.statusmonitor.passnotset'). PHP_EOL
        ;

        $message .= isset($extConf['postUrl']['value']) && ! empty($extConf['postUrl']['value']) 
            ? $this->getLanguageService()->sL($lll . 'task.statusmonitor.urlset')
            : $this->getLanguageService()->sL($lll . 'task.statusmonitor.urlnotset')
        ;
        
        return $message;
    }

    public function execute()
    {
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\Object\ObjectManager::class
        );
        $statusmonitorUtility = $objectManager->get(\FORM4\Statusmonitor\Utility\StatusmonitorUtility::class);
        return $statusmonitorUtility->run();
    }

    /**
     *
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}