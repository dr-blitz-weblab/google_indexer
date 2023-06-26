<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Google Indexer',
    'description' => 'Google Indexer',
    'category' => 'module',
    'author' => '',
    'author_email' => 'office@drblitz-weblab.com',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.0.0-12.9.99',
            'php' => '7.4.0-8.2.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
