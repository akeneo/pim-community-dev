<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

class CallbackProperty extends AbstractProperty
{
    const CALLABLE_KEY = 'callable';

    /** @var array */
    protected $excludeParams = [self::CALLABLE_KEY];

    /**
     * {@inheritdoc}
     */
    public function getRawValue(ResultRecordInterface $record)
    {
        return call_user_func($this->get(self::CALLABLE_KEY), $record);
    }
}
