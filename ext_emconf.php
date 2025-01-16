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
    'version' => '12.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-12.4.99',
        ]
        ,
        'conflicts' => [
        ],
        'suggests' => [
        ]
    ]
]; 
