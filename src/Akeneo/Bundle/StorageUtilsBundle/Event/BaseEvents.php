<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Event;

/**
 * Catalog base events
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class BaseEvents
{
    /**
     * This event is thrown before a generic entity get removed.
     *
     * The event listener receives an
     * Akeneo\Component\StorageUtils\Event\RemoveEvent instance.
     *
     * @staticvar string
     */
    const PRE_REMOVE = 'pim_catalog.pre_remove.object';

    /**
     * This event is thrown before a generic entity get removed.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const POST_REMOVE = 'pim_catalog.post_remove.object';
}
