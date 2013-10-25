<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;

class FieldProperty extends AbstractProperty
{
    /**
     * Get raw value from object
     *
     * @param ResultRecordInterface $record
     *
     * @return mixed
     */
    protected function getRawValue(ResultRecordInterface $record)
    {
        try {
            $value = $record->getValue($this->getOr(self::DATA_NAME_KEY, $this->get(self::NAME_KEY)));
        } catch (\LogicException $e) {
            // default value
            $value = null;
        }

        return $value;
    }
}
