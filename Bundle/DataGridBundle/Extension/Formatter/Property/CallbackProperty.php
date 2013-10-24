<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;

class CallbackProperty extends AbstractProperty
{
    /**
     * {@inheritdoc}
     */
    public function getValue(ResultRecordInterface $record)
    {
        return call_user_func($this->get('callable'), $record);
    }
}
