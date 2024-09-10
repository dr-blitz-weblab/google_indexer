<?php

$GLOBALS['SiteConfiguration']['site']['columns']['google_api_key_path'] = [
    'label' => 'API key',
    'config' => [
        'type' => 'input',
    ]
];

$GLOBALS['SiteConfiguration']['site']['types']['0']['showitem'] .= ',--div--;Google indexer,google_api_key_path';
