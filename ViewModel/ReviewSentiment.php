<?php
declare(strict_types=1);

namespace Macademy\Sentimate\ViewModel;

use Macademy\Sentimate\Model\ReviewSentimentService;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Macademy\Sentimate\Model\ReviewSentimentFactory;

class ReviewSentiment implements ArgumentInterface
{
    /**
     * @param ReviewSentimentService $reviewSentimentService
     */
    public function __construct(
        private readonly ReviewSentimentService $reviewSentimentService
    ) {
    }

    /**
     * @param int $reviewId
     * @param string|null $key
     * @return string|null
     */
    public function getDataByReviewId(
        int $reviewId,
        ?string $key,
    ): ?string {
        try {
            $reviewSentiment = $this->reviewSentimentService->getByReviewId($reviewId);

            return $reviewSentiment->getId()
                ? ucfirst($reviewSentiment->getData($key))
                : null;
        } catch (NoSuchEntityException $exception) {
            // do not do anything
        }

        return null;
    }
}
