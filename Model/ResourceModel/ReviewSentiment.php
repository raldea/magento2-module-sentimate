<?php declare(strict_types=1);

namespace Macademy\Sentimate\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ReviewSentiment extends AbstractDb
{
    /** @var string Main table name */
    const MAIN_TABLE = 'macademy_sentimate_review_sentiment';

    /** @var string Main table primary key field name */
    const ID_FIELD_NAME = 'review_sentiment_id';

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(self::MAIN_TABLE, self::ID_FIELD_NAME);
    }
}
