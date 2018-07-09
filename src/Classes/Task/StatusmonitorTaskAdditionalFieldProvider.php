<?php
namespace FORM4\Statusmonitor\Task;

use FORM4\Statusmonitor\Task\StatusmonitorTask;

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
            'label' => \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('task.statusmonitor.statusmonitorUsername', 'form4_statusmonitor'),
            'label' => 'Username',
            'cshKey' => '',
            'cshLabel' => 'Username'
        ];

        $additionalFields['statusmonitorPassword'] = [
            'code' => '<input type="password" class="form-control" name="tx_scheduler[statusmonitorPassword]" value="' . $taskInfo['statusmonitorPassword'] . '">',
            'label' =>'Password',
            'cshKey' => '',
            'cshLabel' => 'Password'
        ];
        
        $additionalFields['statusmonitorPostUrl'] = [
            'code' => '<input type="text" class="form-control" name="tx_scheduler[statusmonitorPostUrl]" value="' . $taskInfo['statusmonitorPostUrl'] . '">',
            'label' => 'PostUrl (https://)',
            'cshKey' => '',
            'cshLabel' => 'PostUrl (https://)'
        ];
        
        return $additionalFields;
    }
    
    public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task) {
        $task->setStatusmonitorUsername((string) $submittedData['statusmonitorUsername']);
        $task->setStatusmonitorPassword((string) $submittedData['statusmonitorPassword']);
        $task->setStatusmonitorPostUrl((string) $submittedData['statusmonitorPostUrl']);
    }
    
    public function validateAdditionalFields(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject){
        $result = true;
        //no validation for username and password - they might be optional. 
        if(isset($submittedData['statusmonitorPostUrl']) || empty($submittedData['statusmonitorPostUrl'])){
//             throw new \Exception(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('error.noUrl', 'form4_statusmonitor'));
            $result = false;
        }
        return $result;
    }
    
}