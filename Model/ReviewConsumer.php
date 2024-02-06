<?php declare(strict_types=1);

namespace Macademy\Sentimate\Model;

use Exception;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class ReviewConsumer
{
    /**
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param ReviewSentimentFactory $reviewSentimentFactory
     * @param RapidApi $rapidApi
     * @param ReviewSentimentService $reviewSentimentService
     */
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
        private readonly ReviewSentimentFactory $reviewSentimentFactory,
        private readonly RapidApi $rapidApi,
        private readonly ReviewSentimentService $reviewSentimentService
    ) {
    }

    /**
     * @param string $message
     * @return void
     */
    public function process(
        string $message
    ): void {
        $data = $this->preDataForApi($message);

        if (!empty($data)) {
            $unSerializedResponse = $this->rapidApi->getSentimentAnalysis($data['message']);

            if (!empty($unSerializedResponse)) {
                $this->saveResponseToDB($unSerializedResponse, $data['unSerializedData']);
            }
        }
    }

    /**
     * @param string $message
     * @return array
     */
    private function preDataForApi(string $message): array
    {
        try {
            $unSerializedData = $this->serializer->unserialize($message);
            $messageDetail = $unSerializedData['detail'];
            $messageTitle = $unSerializedData['title'];

            return [
                'message' =>  "$messageTitle: $messageDetail",
                'unSerializedData' => $unSerializedData
            ];
        } catch (Exception $exception) {
            $this->logger->error(__('Failed to de-serialize Sentimate Analysis: ' . $exception->getMessage()));
        }

        return [];
    }

    /**
     * @param $unSerializedResponse
     * @param $unSerializedData
     * @return void
     */
    private function saveResponseToDB($unSerializedResponse, $unSerializedData): void
    {
       if ( $this->rapidApi->areSentimentsAnalysisResultsValid($unSerializedResponse)) {
           $reviewSentiment = $this->reviewSentimentFactory->create();
           $reviewSentiment->setData([
               'review_id' => $unSerializedData['review_id'],
               'type' => $unSerializedResponse['type'],
               'score' => $unSerializedResponse['score'],
               'ratio' => $unSerializedResponse['ratio'],
           ]);

           $this->reviewSentimentService->save($reviewSentiment);
       }
    }
}
