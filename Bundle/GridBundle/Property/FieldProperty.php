<?php

namespace Oro\Bundle\GridBundle\Property;

use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

class FieldProperty extends AbstractProperty
{
    /**
     * @var FieldDescriptionInterface
     */
    protected $field;

    /**
     * @param FieldDescriptionInterface $field
     */
    public function __construct(FieldDescriptionInterface $field)
    {
        $this->field = $field;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->field->getName();
    }

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
     * @return mixed
     */
    protected function getRawValue(ResultRecordInterface $record)
    {
        try {
            $value = $record->getValue($this->field->getFieldName());
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
     * @return mixed
     */
    protected function format($value)
    {
        if (null === $value) {
            return $value;
        }

        if (is_object($value) && is_callable(array($value, '__toString'))) {
            $value = $value->__toString();
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
     * @return mixed
     */
    protected function convertValue($value)
    {
        switch ($this->field->getType()) {
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
