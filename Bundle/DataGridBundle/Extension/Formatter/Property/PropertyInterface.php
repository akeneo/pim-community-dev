<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;

interface PropertyInterface
{
    const TYPE_KEY             = 'type';
    const FRONTEND_OPTIONS_KEY = 'frontend_options';

    const NAME_KEY      = 'name';
    const DATA_NAME_KEY = 'data_name';

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
