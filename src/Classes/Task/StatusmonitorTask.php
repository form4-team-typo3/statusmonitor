<?php
namespace FORM4\Statusmonitor\Task;

/*
 * Copyright notice
 *
 * (c) 2018 form4 GmbH & Co. KG <typo3@form4.de>
 * All rights reserved
 *
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility;
use FORM4\Statusmonitor\Utility\StatusmonitorUtility;

/**
 * @author Thomas Grothaus <thomas.grothaus@form4.de>
 */
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
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        
        /** @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility $configurationUtility */
        $configurationUtility = $objectManager->get(ConfigurationUtility::class);
        $extConf = $configurationUtility->getCurrentConfiguration('form4_statusmonitor');
        
        $lll = 'LLL:EXT:form4_statusmonitor/Resources/Private/Language/locallang_db.xlf:';
        
        if(!empty($extConf['user']['value'])){            
            $message = 'User/Id: ' . $extConf['user']['value'] . PHP_EOL;
        }

        $message .= isset($extConf['password']['value']) && ! empty($extConf['password']['value']) 
            ? $this->getLanguageService()->sL($lll . 'task.statusmonitor.passset'). PHP_EOL
            : $this->getLanguageService()->sL($lll . 'task.statusmonitor.passnotset'). PHP_EOL;

        $message .= isset($extConf['postUrl']['value']) && ! empty($extConf['postUrl']['value']) 
            ? $this->getLanguageService()->sL($lll . 'task.statusmonitor.urlset')
            : $this->getLanguageService()->sL($lll . 'task.statusmonitor.urlnotset');
        
        return $message;
    }

    public function execute()
    {
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var \FORM4\Statusmonitor\Utility\StatusmonitorUtility $statusmonitorUtility */
        $statusmonitorUtility = $objectManager->get(StatusmonitorUtility::class);
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