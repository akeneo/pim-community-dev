<?php

namespace Oro\Bundle\GridBundle\Filter;

use Sonata\AdminBundle\Filter\FilterInterface as BaseFilterInterface;

interface FilterInterface extends BaseFilterInterface
{
    /**
     * Allowed filter types
     */
    const TYPE_DATE              = 'oro_grid_orm_date_range';
    const TYPE_DATETIME          = 'oro_grid_orm_datetime_range';
    const TYPE_NUMBER            = 'oro_grid_orm_number';
    const TYPE_STRING            = 'oro_grid_orm_string';
    const TYPE_CHOICE            = 'oro_grid_orm_choice';
    const TYPE_BOOLEAN           = 'oro_grid_orm_boolean';
    const TYPE_ENTITY            = 'oro_grid_orm_entity';
    const TYPE_SELECT_ROW        = 'oro_grid_orm_select_row';
    const TYPE_FLEXIBLE_DATE     = 'oro_grid_orm_flexible_date_range';
    const TYPE_FLEXIBLE_DATETIME = 'oro_grid_orm_flexible_datetime_range';
    const TYPE_FLEXIBLE_NUMBER   = 'oro_grid_orm_flexible_number';
    const TYPE_FLEXIBLE_BOOLEAN  = 'oro_grid_orm_flexible_boolean';
    const TYPE_FLEXIBLE_STRING   = 'oro_grid_orm_flexible_string';
    const TYPE_FLEXIBLE_OPTIONS  = 'oro_grid_orm_flexible_options';
    const TYPE_FLEXIBLE_ENTITY   = 'oro_grid_orm_flexible_entity';

    /**
     * @return boolean
     */
    public function isActive();

    /**
     * @return boolean
     */
    public function isNullable();
}
