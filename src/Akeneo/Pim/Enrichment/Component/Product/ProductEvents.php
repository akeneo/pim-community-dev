<?php

namespace Akeneo\Pim\Enrichment\Component\Product;

/**
 * Catalog product events
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ProductEvents
{
    /**
     * This event is thrown each time a product is created in the system.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const CREATE = 'pim_catalog.create_product';

    /**
     * This event is thrown before several products get removed
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_MASS_REMOVE = 'pim_catalog.pre_mass_remove.product';

    /**
     * This event is thrown after several products have been removed.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const POST_MASS_REMOVE = 'pim_catalog.post_mass_remove.product';
}
