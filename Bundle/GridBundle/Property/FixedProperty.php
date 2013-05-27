<?php

namespace Oro\Bundle\GridBundle\Property;

use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;

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
