<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'form4 Status Monitor',
    'description' => 'Sends scheduled JSON POST requests to a configurable URL with TYPO3 core version, locally installed extensions, and optional server metadata—for centralized monitoring and inventory.',
    'category' => 'be',
    'author' => 'form4 GmbH',
    'author_email' => 'typo3@form4.de',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '14.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '14.3.0-14.3.99',
        ]
        ,
        'conflicts' => [
        ],
        'suggests' => [
        ]
    ]
];
