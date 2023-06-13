<?php

namespace DrBlitz\GoogleIndexer\Enumeration;

class GoogleApi extends \TYPO3\CMS\Core\Type\Enumeration
{
    public const __default = self::UPDATE;

    public const UPDATE = 'URL_UPDATED';

    public const REMOVE = 'URL_DELETED';
}
