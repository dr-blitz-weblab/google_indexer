<?php

namespace DrBlitz\GoogleIndexer\Service;

use DrBlitz\GoogleIndexer\Enumeration\GoogleApi;
use Google\Service\Indexing;
use Google_Client;
use GuzzleHttp\Exception\GuzzleException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class GoogleIndexingApi implements SingletonInterface
{
    private const API_URL = 'https://indexing.googleapis.com/v3';
    private string $configFile = '';

    /**
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function __construct()
    {
        $this->configFile = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('google_indexer', 'config_file_path');
    }

    public static function isConfigFileExist()
    {

    }

    /**
     * @param string $url
     * @param GoogleApi $type
     * @return array
     * @throws GuzzleException
     */
    public function execute(string $url, GoogleApi $type): array
    {
        try {
            $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
            $site = $siteFinder->getSiteByPageId($_REQUEST['id']);
            $jsonKey = $site->getConfiguration()['google_api_key_path'];

            $client = new Google_Client();
            $client->setAuthConfig($jsonKey);
            $client->addScope(Indexing::INDEXING);

            // Get a Guzzle HTTP Client
            $httpClient = $client->authorize();
            $endpoint = self::API_URL . '/urlNotifications:publish';

            $content = [
                'url' => $url,
                'type' => $type->__toString(),
            ];
            $response = $httpClient->post($endpoint, ['body' => json_encode($content)]);
            return [
                'status' => $response->getStatusCode(),
                'message' => $response->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param string $content
     * @param string $type
     * @return mixed
     */
    private function getMessage(string $content, string $type)
    {
        $content = json_decode($content);
        if ($type === GoogleApi::REMOVE) {
            return $content->urlNotificationMetadata->latestRemove->type;
        }
        return $content->urlNotificationMetadata->latestUpdate->type;
    }

    /**
     * @param string $content
     * @return mixed
     */
    private function getErrorMessage(string $content)
    {
        $content = json_decode($content);
        return $content->error->message;

    }

    /**
     * @param $url
     * @return array
     * @throws GuzzleException
     */
    public function getNotificationStatus($url): array
    {
        try {
            $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
            $site = $siteFinder->getSiteByPageId($_REQUEST['id']);
            $jsonKey = $site->getConfiguration()['google_api_key_path'];

            $client = new Google_Client();
            $client->setAuthConfig($jsonKey);
            $client->addScope(Indexing::INDEXING);

            $httpClient = $client->authorize();
            $endpoint = self::API_URL . '/urlNotifications/metadata?url=' . urlencode($url);
            $response = $httpClient->get($endpoint);

            return [
                'status' => $response->getStatusCode(),
                'message' => $response->getBody()->getContents(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }

    }
}
