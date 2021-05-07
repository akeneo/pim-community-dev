<?php

namespace Oro\Bundle\DataGridBundle\Extension\Formatter\Property;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;

/**
 * Interface PropertyInterface
 * @package Oro\Bundle\DataGridBundle\Extension\Formatter\Property
 *
 * Every property should be stateless
 */
interface PropertyInterface
{
    public const TYPE_DATE = 'date';
    public const TYPE_DATETIME = 'datetime';
    public const TYPE_DECIMAL = 'decimal';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_PERCENT = 'percent';
    public const TYPE_SELECT = 'select';
    public const TYPE_STRING = 'string';
    public const TYPE_HTML = 'html';
    public const TYPE_BOOLEAN = 'boolean';

    public const METADATA_NAME_KEY = 'name';
    public const METADATA_TYPE_KEY = 'type';

    public const TYPE_KEY = 'type';
    public const NAME_KEY = 'name';
    public const DATA_NAME_KEY = 'data_name';
    public const FRONTEND_TYPE_KEY = 'frontend_type';

    /**
     * Initialize property for each cell
     *
     * @param PropertyConfiguration $params
     *
     * @return $this
     */
    public function init(PropertyConfiguration $params);

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
