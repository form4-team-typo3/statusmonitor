<?php
namespace FORM4\Statusmonitor\Task;

use FORM4\Statusmonitor\Task\StatusmonitorTask;
use TYPO3\CMS\Core\Messaging\FlashMessage;

class StatusmonitorTaskAdditionalFieldProvider implements \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface
{
    protected $lll = 'LLL:EXT:form4_statusmonitor/Resources/Private/Language/locallang_db.xlf:';
                     
    
    public function getAdditionalFields(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {
        
        if (empty($taskInfo['statusmonitorUsername'])) {
            if (empty($task->statusmonitorUsername)) {
                $taskInfo['statusmonitorUsername']= '';
            } else {
                $taskInfo['statusmonitorUsername'] = $task->statusmonitorUsername;
            }
        }
        
        if (empty($taskInfo['statusmonitorPassword'])) {
            if (empty($task->statusmonitorPassword)) {
                $taskInfo['statusmonitorPassword']= '';
            } else {
                $taskInfo['statusmonitorPassword'] = $task->statusmonitorPassword;
            }
        }
        
        if (empty($taskInfo['statusmonitorPostUrl'])) {
            if (empty($task->statusmonitorPostUrl)) {
                $taskInfo['statusmonitorPostUrl']= '';
            } else {
                $taskInfo['statusmonitorPostUrl'] = $task->statusmonitorPostUrl;
            }
        }
        
        
        $additionalFields['statusmonitorUsername'] = [
            'code' => '<input type="text" class="form-control" name="tx_scheduler[statusmonitorUsername]" value="' . $taskInfo['statusmonitorUsername'] . '">',
            'label' => $this->getLanguageService()->sL('LLL:EXT:form4_statusmonitor/Resources/Private/Language/locallang_db.xlf:task.statusmonitor.statusmonitorUsername'),
            'cshKey' => '',
            'cshLabel' => ''
        ];

        $additionalFields['statusmonitorPassword'] = [
            'code' => '<input type="password" class="form-control" name="tx_scheduler[statusmonitorPassword]" value="' . $taskInfo['statusmonitorPassword'] . '">',
            'label' =>$this->getLanguageService()->sL('LLL:EXT:form4_statusmonitor/Resources/Private/Language/locallang_db.xlf:task.statusmonitor.statusmonitorPassword'),
            'cshKey' => '',
            'cshLabel' => ''
        ];
        
        $additionalFields['statusmonitorPostUrl'] = [
            'code' => '<input type="text" class="form-control" name="tx_scheduler[statusmonitorPostUrl]" value="' . $taskInfo['statusmonitorPostUrl'] . '">',
            'label' => $this->getLanguageService()->sL('LLL:EXT:form4_statusmonitor/Resources/Private/Language/locallang_db.xlf:task.statusmonitor.statusmonitorPostUrl'),
            'cshKey' => '',
            'cshLabel' => ''
        ];
        
        return $additionalFields;
    }
    public function validateAdditionalFields(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject){
        $result = true;
        //no validation for username and password - they might be optional. 
        if(!isset($submittedData['statusmonitorPostUrl']) || empty($submittedData['statusmonitorPostUrl'])){
            $parentObject->addMessage(
                $this->getLanguageService()->sL('LLL:EXT:form4_statusmonitor/Resources/Private/Language/locallang_db.xlf:error.noUrl'),
                FlashMessage::ERROR
            );
            $result = false;
        }
        return $result;
    }
    
    public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task) {
        $task->statusmonitorUsername = $submittedData['statusmonitorUsername'];
        $task->statusmonitorPassword = $submittedData['statusmonitorPassword'];
        $task->statusmonitorPostUrl = $submittedData['statusmonitorPostUrl'];
    }
    
    /**
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
    
}