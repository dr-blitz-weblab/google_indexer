<?php

namespace DrBlitz\GoogleIndexer\Service;

use DrBlitz\GoogleIndexer\Enumeration\GoogleApi;
use DrBlitz\GoogleIndexer\Utility\Extension;
use Google\Service\Indexing;
use GuzzleHttp\Exception\GuzzleException;
use TYPO3\CMS\Core\SingletonInterface;

final class GoogleIndexingApi implements SingletonInterface
{
    private const API_URL = 'https://indexing.googleapis.com/v3';

    private string $configFile = '';
    public function __construct(int $pageId = 1)
    {
        $this->configFile = Extension::getConfigFile($pageId);
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
            $pid = $_REQUEST['id'] ?? $_REQUEST['cmd']['pages'] ?? null;
            if (is_array($pid)) {
                $pid = array_key_first($pid);
            }

            if (!$pid) {
                return [
                    'status' => '404',
                    'message' => 'Site with this page id not found',
                ];
            }

            $client = new \Google_Client();
            $client->setAuthConfig($this->configFile);
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
            $client = new \Google_Client();
            $client->setAuthConfig($this->configFile);
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
