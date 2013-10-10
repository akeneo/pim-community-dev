<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;

interface PropertyInterface
{
    /**
     * Get value name
     *
     * @return string
     */
    public function getName();

    /**
     * Get field value from data
     *
     * @param ResultRecordInterface $record
     * @return mixed
     */
    public function getValue(ResultRecordInterface $record);
}
