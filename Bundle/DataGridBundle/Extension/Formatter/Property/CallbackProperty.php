<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;

class CallbackProperty extends AbstractProperty
{
    /**
     * @var string
     */
    protected $valueKey;

    /**
     * @var callback
     */
    protected $callback;

    /**
     * @param string $name
     * @param callback $callback
     */
    public function __construct($name, $callback)
    {
        $this->name     = $name;
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(ResultRecordInterface $record)
    {
        return call_user_func($this->callback, $record);
    }
}
