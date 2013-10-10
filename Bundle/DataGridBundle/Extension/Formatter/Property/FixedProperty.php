<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;

class FixedProperty extends AbstractProperty
{
    /**
     * @var string
     */
    protected $valueKey;

    /**
     * @param string $name
     * @param string $valueKey
     */
    public function __construct($name, $valueKey = null)
    {
        $this->name = $name;
        $this->valueKey = $valueKey ? $valueKey : $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(ResultRecordInterface $record)
    {
        return $record->getValue($this->valueKey);
    }
}
