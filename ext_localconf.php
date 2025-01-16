<?php
defined('TYPO3') or die();

$lll = 'LLL:EXT:form4_statusmonitor/Resources/Private/Language/locallang_db.xlf:';

// Register scheduler task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\FORM4\Statusmonitor\Task\StatusmonitorTask::class] = [
    'extension' => 'form4_statusmonitor',
    'title' => $lll . 'statusMonitorTask.name',
    'description' => $lll . 'statusMonitorTask.description'
];
