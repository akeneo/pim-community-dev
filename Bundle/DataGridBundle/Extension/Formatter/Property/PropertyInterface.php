<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;

interface PropertyInterface
{
    /**
     * Prepare state for property state for current field
     *
     * @param $params
     *
     * @return mixed
     */
    public function init(array $params);

    /**
     * Get field value from data
     *
     * @param ResultRecordInterface $record
     *
     * @return mixed
     */
    public function getValue(ResultRecordInterface $record);
}
