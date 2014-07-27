<?php

namespace Pim\Bundle\CatalogBundle\Event;

/**
 * Catalog category events
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class CategoryEvents
{
    /**
     * This event is thrown before a category is removed.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_REMOVE_CATEGORY = 'pim_catalog.pre_remove.category';

    /**
     * This event is thrown before a tree is removed.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_REMOVE_TREE = 'pim_catalog.pre_remove.tree';
}
