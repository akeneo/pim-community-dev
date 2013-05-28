<?php
namespace Pim\Bundle\ProductBundle\Filter;

use Oro\Bundle\GridBundle\Filter\FilterInterface as OroFilterInterface;

/**
 * Overriding OroFilterInteface
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
    const TYPE_LOCALE = 'pim_grid_orm_locale';
}