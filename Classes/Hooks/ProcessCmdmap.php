<?php

namespace DrBlitz\GoogleIndexer\Hooks;

use DrBlitz\GoogleIndexer\Enumeration\GoogleApi;
use DrBlitz\GoogleIndexer\Service\GoogleIndexingApi;
use DrBlitz\GoogleIndexer\Utility\Extension;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ProcessCmdmap
{
    public function processCmdmap_deleteAction(
        string      $table,
        int         $id,
        array       $recordToDelete,
        bool        $recordWasDeleted,
        DataHandler $dataHandler
    ): void
    {
        $allowedDokType = Extension::getAllDokType();
        if ($table === 'pages' && in_array($recordToDelete['doktype'], $allowedDokType) && Extension::isConfigFileExist($id)) {
            $pages = Extension::getPage($id, $allowedDokType);
            $apiService = GeneralUtility::makeInstance(GoogleIndexingApi::class, $id);
            foreach ($pages as $page) {
                $url = Extension::getFrontendUrl($page['uid'], $page['sys_language_uid']);
                $response = $apiService->execute($url, GoogleApi::cast('URL_DELETED'));
                if ($response['status'] !== 200) {
                    $severity = ContextualFeedbackSeverity::ERROR;
                    $messageText = LocalizationUtility::translate('delete_page_hooks_error', 'google_indexer');
                    $message = GeneralUtility::makeInstance(
                        FlashMessage::class,
                        $messageText,
                        '',
                        $severity
                    );
                    $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
                    $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
                    $messageQueue->addMessage($message);
                }
            }

        }
    }


    public function processDatamap_preProcessFieldArray(
        array       &$incomingFieldArray,
        string      $table,
        string      $id,
        DataHandler $dataHandler
    ): void
    {
        if ($table !== 'pages') {
            return;
        }
        if (!\is_int($id)) {
            $incomingFieldArray['googleindexer_executetime'] = 0;
            $incomingFieldArray['googleindexer_last_api_answer'] = '';
        }
    }

}
