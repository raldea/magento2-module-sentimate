<?php declare(strict_types=1);

namespace Macademy\Sentimate\Model;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Laminas\Uri\Http;
use Magento\Framework\Serialize\SerializerInterface;

class RapidApi
{
    const RAPID_API_CONFIG_PATH_API_KEY = 'macademy_sentimate/rapidapi/api_key';

    /**
     * @param GuzzleClient $guzzleClient
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param SerializerInterface $serializer
     * @param Http $http
     */
    public function __construct(
        private readonly GuzzleClient $guzzleClient,
        private readonly LoggerInterface $logger,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly EncryptorInterface $encryptor,
        private readonly SerializerInterface $serializer,
        private Http $http
    ) {
    }

    /**
     * @param string $endpoint
     * @param array $formParams
     * @return array|null
     */
    private function rapidApiPostRequest(
        string $endpoint,
        array $formParams = []
    ): ?array {
        try {
            $client = $this->guzzleClient;
            $decryptedApiKey = $this->getApiKey();
            $url = $this->http->parse($endpoint);
            $apiHost = $url->getHost();

            $response = $client->request('POST', $endpoint, [
                'form_params' => $formParams,
                'headers' => [
                    'X-RapidAPI-Host' => $apiHost,
                    'X-RapidAPI-Key' => $decryptedApiKey,
                    'content-type' => 'application/x-www-form-urlencoded',
                ],
            ]);
            $body = $response->getBody();

            return $this->serializer->unserialize($body);
        } catch (GuzzleException $guzzleException) {
            $this->logger->error(__("$endpoint returned an error: " . $guzzleException->getMessage()));
        }

        return [];
    }

    /**
     * @return string
     */
    private function getApiKey(): string
    {
        $apiKey = $this->scopeConfig->getValue(self::RAPID_API_CONFIG_PATH_API_KEY);

        return $this->encryptor->decrypt($apiKey);
    }

    /**
     * @param string $text
     * @return array|null
     */
    public function getSentimentAnalysis(string $text): ?array
    {
        $url = 'https://twinword-sentiment-analysis.p.rapidapi.com/analyze/';
        $formParams = [
          'text' => $text
        ];

        $result = $this->rapidApiPostRequest($url, $formParams);
        $this->logInvalidSentimentAnalysisResults($result);

        return $result;
    }

    /**
     * @param $result
     * @return void
     */
    private function logInvalidSentimentAnalysisResults($result): void
    {
        if (!$this->areSentimentsAnalysisResultsValid($result)) {
            $stringResponse = implode(', ', $result);
            $this->logger->error(
                __('Sentiment Analysis API did not return expected results: %1', $stringResponse)
            );
        }
    }

    /**
     * @param $result
     * @return bool
     */
    public function areSentimentsAnalysisResultsValid($result): bool
    {
        return is_array($result) && isset($result['type'], $result['score'], $result['ratio']);
    }
}
