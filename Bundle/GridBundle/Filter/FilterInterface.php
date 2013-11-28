<?php

namespace Oro\Bundle\GridBundle\Filter;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

interface FilterInterface
{
    /**
     * Allowed filter types
     */
    const TYPE_DATE              = 'oro_grid_orm_date_range';
    const TYPE_DATETIME          = 'oro_grid_orm_datetime_range';
    const TYPE_NUMBER            = 'oro_grid_orm_number';
    const TYPE_PERCENT           = 'oro_grid_orm_percent';
    const TYPE_STRING            = 'oro_grid_orm_string';
    const TYPE_CHOICE            = 'oro_grid_orm_choice';
    const TYPE_BOOLEAN           = 'oro_grid_orm_boolean';
    const TYPE_ENTITY            = 'oro_grid_orm_entity';
    const TYPE_SELECT_ROW        = 'oro_grid_orm_select_row';

    /**
     * @return boolean
     */
    public function isActive();

    /**
     * @return boolean
     */
    public function isNullable();

    /**
     * Apply the filter to the QueryBuilder instance
     *
     * @param ProxyQueryInterface $queryBuilder
     * @param string              $alias
     * @param string              $field
     * @param string              $value
     *
     * @return void
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $value);

    /**
     * @param mixed $query
     * @param mixed $value
     */
    public function apply($query, $value);

    /**
     * Returns the filter name
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the label name
     *
     * @return string
     */
    public function getLabel();

    /**
     * @param string $label
     */
    public function setLabel($label);

    /**
     * @return array
     */
    public function getDefaultOptions();

    /**
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    public function getOption($name, $default = null);

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setOption($name, $value);

    /**
     * @param string $name
     * @param array  $options
     *
     * @return void
     */
    public function initialize($name, array $options = array());

    /**
     * @return string
     */
    public function getFieldName();

    /**
     * @return array of mappings
     */
    public function getParentAssociationMappings();

    /**
     * @return array field mapping
     */
    public function getFieldMapping();

    /**
     * @return array association mapping
     */
    public function getAssociationMapping();

    /**
     * @return array
     */
    public function getFieldOptions();

    /**
     * @return string
     */
    public function getFieldType();

    /**
     * Returns the main widget used to render the filter
     *
     * @return array
     */
    public function getRenderSettings();
}
