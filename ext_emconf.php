<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Google Indexer',
    'description' => 'Notify google when you create new pages directly from TYPO3. Supported options: Update, remove and check status',
    'category' => 'module',
    'author' => 'Krzysztof Napora',
    'author_company' => 'DR BLITZ WEBLAB',
    'author_email' => 'office@drblitz-weblab.com',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'version' => '1.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '11.0.0-11.9.99',
            'php' => '7.4.0-8.2.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
