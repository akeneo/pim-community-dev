<?php

namespace Pim\Bundle\CatalogBundle\Event;

/**
 * Catalog association type events
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class AssociationTypeEvents
{
    /**
     * This event is thrown before an association type gets removed.
     *
     * The event listener receives an
     * Akeneo\Component\StorageUtils\Event\RemoveEvent instance.
     *
     * @staticvar string
     */
    const PRE_REMOVE = 'pim_catalog.pre_remove.association_type';

    /**
     * This event is thrown after an association type gets removed.
     *
     * The event listener receives an
     * Akeneo\Component\StorageUtils\Event\RemoveEvent instance.
     *
     * @staticvar string
     */
    const POST_REMOVE = 'pim_catalog.post_remove.association_type';
}
