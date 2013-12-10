<?php

namespace Pim\Bundle\GridBundle\Filter;

use Oro\Bundle\GridBundle\Filter\FilterInterface as OroFilterInterface;

/**
 * Overriding OroFilterInterface
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FilterInterface extends OroFilterInterface
{
    /**
     * Allowed filter types
     * @staticvar string
     */
    const TYPE_FLEXIBLE_DATE     = 'pim_grid_orm_flexible_date_range';
    const TYPE_FLEXIBLE_DATETIME = 'pim_grid_orm_flexible_datetime_range';
    const TYPE_FLEXIBLE_NUMBER   = 'pim_grid_orm_flexible_number';
    const TYPE_FLEXIBLE_BOOLEAN  = 'pim_grid_orm_flexible_boolean';
    const TYPE_FLEXIBLE_STRING   = 'pim_grid_orm_flexible_string';
    const TYPE_FLEXIBLE_OPTIONS  = 'pim_grid_orm_flexible_options';
    const TYPE_FLEXIBLE_ENTITY   = 'pim_grid_orm_flexible_entity';
    const TYPE_CURRENCY          = 'pim_grid_orm_currency';
    const TYPE_SCOPE             = 'pim_grid_orm_scope';
    const TYPE_COMPLETENESS      = 'pim_grid_orm_completeness';
    const TYPE_METRIC            = 'pim_grid_orm_metric';
}
