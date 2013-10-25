<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;

interface PropertyInterface
{
    const TYPE_DATE     = 'date';
    const TYPE_DATETIME = 'datetime';
    const TYPE_DECIMAL  = 'decimal';
    const TYPE_INTEGER  = 'integer';
    const TYPE_PERCENT  = 'percent';
    const TYPE_SELECT   = 'select';
    const TYPE_STRING   = 'string';
    const TYPE_HTML     = 'html';
    const TYPE_BOOLEAN  = 'boolean';

    const METADATA_NAME_KEY = 'name';
    const METADATA_TYPE_KEY = 'type';

    const TYPE_KEY             = 'type';
    const NAME_KEY             = 'name';
    const DATA_NAME_KEY        = 'data_name';
    const FRONTEND_TYPE_KEY    = 'frontend_type';

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

    /**
     * Returns field metadata
     *
     * @return array
     */
    public function getMetadata();
}
