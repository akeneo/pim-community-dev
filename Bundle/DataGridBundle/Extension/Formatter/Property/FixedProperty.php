<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;

class FixedProperty extends AbstractProperty
{
    /** @var array */
    protected $excludeParams = [self::DATA_NAME_KEY];

    /**
     * {@inheritdoc}
     */
    public function getValue(ResultRecordInterface $record)
    {
        return $record->getValue($this->get(self::DATA_NAME_KEY));
    }
}
