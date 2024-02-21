<?php

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = \DrBlitz\GoogleIndexer\Hooks\ProcessCmdmap::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \DrBlitz\GoogleIndexer\Hooks\ProcessCmdmap::class;
