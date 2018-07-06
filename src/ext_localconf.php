<?php

$lll = 'LLL:EXT:' . $_EXTKEY . '/locallang_db.xlf:';

// Add caching framework garbage collection task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\FORM4\form4Statusmonitor\Task\StatusmonitorTask::class] = array(
    'extension' => $_EXTKEY,
    'title' => $lll . 'statusMonitorTask.name',
    'description' => '$lll . statusMonitorTask.description',
    'additionalFields' => \TYPO3\CMS\Scheduler\Task\CachingFrameworkGarbageCollectionAdditionalFieldProvider::class
);