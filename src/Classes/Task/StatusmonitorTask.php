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
        $message = '';
        $message .= 'User/Id :' . $this->statusmonitorUsername . PHP_EOL;
        
        $lll = 'LLL:EXT:form4_statusmonitor/Resources/Private/Language/locallang_db.xlf:';
        $passSetPhrase = $this->getLanguageService()->sL($lll . 'task.statusmonitor.passset');
        $passNotSetPhrase = $this->getLanguageService()->sL($lll . 'task.statusmonitor.passnotset');
        $passInfo = isset($this->statusmonitorPassword) && ! empty($this->statusmonitorPassword) ? $passSetPhrase : $passNotSetPhrase;
        
        $message .= $passInfo . PHP_EOL;
        $message .= $this->statusmonitorPostUrl;
        return $message;
    }

    public function execute()
    {
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager */
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\Object\ObjectManager::class);
        $statusmonitorUtility = $objectManager->get(\FORM4\Statusmonitor\Utility\StatusmonitorUtility::class);
        return $statusmonitorUtility->run($this->statusmonitorPassword, $this->statusmonitorUsername,
            $this->statusmonitorPostUrl);
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