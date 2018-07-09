<?php

$lll = 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_db.xlf:';

// Add caching framework garbage collection task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\FORM4\Statusmonitor\Task\StatusmonitorTask::class] = array(
    'extension' => $_EXTKEY,
    'title' => $lll . 'statusMonitorTask.name',
    'description' => $lll . 'statusMonitorTask.description',
    'additionalFields' => \FORM4\Statusmonitor\Task\StatusmonitorTaskAdditionalFieldProvider::class 
);