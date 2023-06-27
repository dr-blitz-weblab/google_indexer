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
    'version' => '2.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.0.0-12.9.99',
            'php' => '8.0.0-8.2.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
