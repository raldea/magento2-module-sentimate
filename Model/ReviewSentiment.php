<?php declare(strict_types=1);

namespace Macademy\Sentimate\Model;

use Magento\Framework\Model\AbstractModel;

class ReviewSentiment extends AbstractModel
{
    /** @var string Main table primary key field name */
    protected $_idFieldName = ResourceModel\ReviewSentiment::ID_FIELD_NAME;

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel\ReviewSentiment::class);
    }
}
