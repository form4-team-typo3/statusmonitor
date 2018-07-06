<?php
namespace FORM4\Statusmonitor\Task;

class StatusmonitorTaskAdditionalFieldProvider implements \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface
{
    protected $lll = 'LLL:EXT:form4_statusmonitor/Resources/Private/Language/locallang_db.xlf:';
    
    public function getAdditionalFields(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {
        //$password,$username,$postUrl
        
        
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
            'label' => $lll . 'task.statusmonitor.statusmonitorUsername',
            'cshKey' => '',
            'cshLabel' => 'task.statusmonitor.statusmonitorUsername'
        ];

        $additionalFields['statusmonitorPassword'] = [
            'code' => '<input type="text" class="form-control" name="tx_scheduler[statusmonitorPassword]" value="' . $taskInfo['statusmonitorPassword'] . '">',
            'label' => $lll . 'task.statusmonitor.statusmonitorPassword',
            'cshKey' => '',
            'cshLabel' => 'task.statusmonitor.statusmonitorPassword'
        ];
        
        $additionalFields['statusmonitorPostUrl'] = [
            'code' => '<input type="text" class="form-control" name="tx_scheduler[statusmonitorPostUrl]" value="' . $taskInfo['statusmonitorPostUrl'] . '">',
            'label' => $lll . 'task.statusmonitor.statusmonitorPostUrl',
            'cshKey' => '',
            'cshLabel' => 'task.statusmonitor.statusmonitorPostUrl'
        ];
        $additionalFields['statusmonitorUsername'] = [
            'code' => '<input type="text" class="form-control" name="tx_scheduler[statusmonitorUsername]" value="' . $taskInfo['statusmonitorUsername'] . '">',
            'label' => $lll . 'task.statusmonitor.username',
            'cshKey' => '',
            'cshLabel' => 'task.statusmonitor.username'
        ];
        
        return $additionalFields;
    }
    
    public function validateAdditionalFieldMultiplikator($value, SchedulerModuleController $schedulerModule){
//         $result = true;
//         $value = floatval($value);
//         if(!is_float($value) || empty($value)){
//             $result = false;
//             $schedulerModule->addMessage(
//                 $this->getLanguageService()->sL('LLL:EXT:corporate/Resources/Private/Language/locallang_db.xlf:task.readtime.readtimeMultiplikator.error'),
//                 FlashMessage::ERROR
//                 );
//         }
        return $result;
    }
    
    public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task) {
        
//         if (!$task instanceof ReadtimeTask) {
//             throw new \InvalidArgumentException(
//                 'Expected a task of type \TYPO3\CMS\Recycler\Task\ReadtimeTask, but got ' . get_class($task),
//                 1510155992
//                 );
//         }
        $task->setMultiplikator((float) $submittedData['readtimeMultiplikator']);
    }
    
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule) {
        return $this->validateAdditionalFieldMultiplikator($submittedData['readtimeMultiplikator'], $schedulerModule);
    }
    
    /**
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}