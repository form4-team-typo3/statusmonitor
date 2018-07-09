<?php
namespace FORM4\Statusmonitor\Task;

class StatusmonitorTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask{
    
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
        $message .=  $this->statusmonitorPassword . $this->statusmonitorUsername . $this->statusmonitorPostUrl;
        return $message;
    }
    
    public function execute(){
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager */
    	$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
    	$statusmonitorUtility = $objectManager->get(\FORM4\Statusmonitor\Utility\StatusmonitorUtility::class);
    	$statusmonitorUtility->run($statusmonitorPassword,$statusmonitorUsername,$statusmonitorPostUrl);
    }
    
    public function setStatusmonitorUsername($username){
        $this->statusmonitorPostUrl = $username;    
    }
    
    public function setStatusmonitorPassword($password){
        $this->statusmonitorPassword = $password;
    }
    
    public function setStatusmonitorPostUrl($url){
        $this->statusmonitorPostUrl = $url;
    }
    
}