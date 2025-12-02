<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'FORM4  Status Monitor',
    'description' => 'This Extension send a JSON Object Post to a given url, with Informations about the actual typo3 installation and components.',
    'category' => 'be',
    'author' => 'form4 GmbH & Co. KG',
    'author_email' => 'typo3@form4.de',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0', 
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '13.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.4.99',
        ]
        ,
        'conflicts' => [
        ],
        'suggests' => [
        ]
    ]
]; 
