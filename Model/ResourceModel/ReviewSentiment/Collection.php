<?php declare(strict_types=1);

namespace Macademy\Sentimate\Model\ResourceModel\ReviewSentiment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Macademy\Sentimate\Model\ReviewSentiment;
use Macademy\Sentimate\Model\ResourceModel\ReviewSentiment as ResourceModelReviewSentiment;

class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(ReviewSentiment::class, ResourceModelReviewSentiment::class);
    }
}
