<?php

namespace Pim\Bundle\GridBundle\Filter;

use Oro\Bundle\GridBundle\Filter\FilterInterface as OroFilterInterface;

/**
 * Overriding OroFilterInterface
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
interface FilterInterface extends OroFilterInterface
{
    /**
     * Allowed filter types
     * @staticvar string
     */
    const TYPE_CURRENCY = 'pim_grid_orm_currency';
    const TYPE_LOCALE   = 'pim_grid_orm_locale';
    const TYPE_SCOPE    = 'pim_grid_orm_scope';
}
