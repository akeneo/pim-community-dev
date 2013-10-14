<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;

class FieldProperty extends AbstractProperty
{
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
            $value = $record->getValue($this->get('name'));
        } catch (\LogicException $e) {
            // default value if there is no flexible attribute
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

        // TODO : to fix the case where $value is a flexible value
        if (is_object($value) && is_callable(array($value, '__toString'))) {
            $value = $value->__toString();
        } elseif (false === $value && $this->getOr('flexible_name')) {
            return null;
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
        switch ($this->getOr('frontend_type')) {
            case FieldDescriptionInterface::TYPE_DATETIME:
            case FieldDescriptionInterface::TYPE_DATE:
                if ($value instanceof \DateTime) {
                    $value = $value->format(\DateTime::ISO8601);
                }
                $result = (string)$value;
                break;
            case FieldDescriptionInterface::TYPE_TEXT:
                $result = (string)$value;
                break;
            case FieldDescriptionInterface::TYPE_DECIMAL:
                $result = floatval($value);
                break;
            case FieldDescriptionInterface::TYPE_INTEGER:
                $result = intval($value);
                break;
            case FieldDescriptionInterface::TYPE_BOOLEAN:
                $result = (bool)$value;
                break;
            default:
                $result = $value;
        }

        return $result;
    }
}
