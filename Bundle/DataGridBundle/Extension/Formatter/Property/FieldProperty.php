<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;

class FieldProperty extends AbstractProperty
{
    const FRONTEND_TYPE_KEY = 'frontend_type';

    const TYPE_DATE     = 'date';
    const TYPE_DATETIME = 'datetime';
    const TYPE_DECIMAL  = 'decimal';
    const TYPE_INTEGER  = 'integer';
    const TYPE_PERCENT  = 'percent';
    const TYPE_OPTIONS  = 'options';
    const TYPE_TEXT     = 'text';
    const TYPE_HTML     = 'html';
    const TYPE_BOOLEAN  = 'boolean';

    /**
     * {@inheritdoc}
     */
    public function getValue(ResultRecordInterface $record)
    {
        return $this->format($this->getRawValue($record));
    }

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

    /**
     * Format raw value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function format($value)
    {
        if (null === $value) {
            return $value;
        }

        $result = $this->convertValue($value);

        if (is_object($result) && is_callable(array($result, '__toString'))) {
            $result = (string)$result;
        }

        return $result;
    }

    /**
     * Convert value to appropriate type
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function convertValue($value)
    {
        switch ($this->getOr(self::FRONTEND_TYPE_KEY)) {
            case self::TYPE_DATETIME:
            case self::TYPE_DATE:
                if ($value instanceof \DateTime) {
                    $value = $value->format(\DateTime::ISO8601);
                }
                $result = (string)$value;
                break;
            case self::TYPE_TEXT:
                $result = (string)$value;
                break;
            case self::TYPE_DECIMAL:
                $result = floatval($value);
                break;
            case self::TYPE_INTEGER:
                $result = intval($value);
                break;
            case self::TYPE_BOOLEAN:
                $result = (bool)$value;
                break;
            default:
                $result = $value;
        }

        return $result;
    }
}
