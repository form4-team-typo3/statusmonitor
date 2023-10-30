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
use FORM4\Statusmonitor\Utility\StatusmonitorUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * @author Thomas Grothaus <thomas.grothaus@form4.de>
 */
class StatusmonitorTask extends AbstractTask
{
    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $pass;

    /**
     * @var string
     */
    private $posturl;

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
    public function getAdditionalInformation(): string
    {
        $languageService = $GLOBALS['LANG'];

        /**
         * @var ExtensionConfiguration $extensionConfiguration
         */
        $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $extConf = $extensionConfiguration->get('form4_statusmonitor');
        $this->pass = $extConf['password'];
        $this->posturl = $extConf['postUrl'];
        $this->user = $extConf['user'];

        $lll = 'LLL:EXT:form4_statusmonitor/Resources/Private/Language/locallang_db.xlf:';

        if(!empty($this->user)){
            $message = 'User/Id: ' . $this->user . PHP_EOL;
        }

        $message .= isset($this->pass) && ! empty($this->pass)
            ? $languageService->sL($lll . 'task.statusmonitor.passset'). PHP_EOL
            : $languageService->sL($lll . 'task.statusmonitor.passnotset'). PHP_EOL;

        $message .= isset($this->posturl) && ! empty($this->posturl)
            ? $languageService->sL($lll . 'task.statusmonitor.urlset')
            : $languageService->sL($lll . 'task.statusmonitor.urlnotset');

        return $message;
    }

    public function execute(): bool
    {
        /** @var \FORM4\Statusmonitor\Utility\StatusmonitorUtility $statusmonitorUtility */
        $statusmonitorUtility = GeneralUtility::makeInstance(StatusmonitorUtility::class);
        return $statusmonitorUtility->run();
    }

}
