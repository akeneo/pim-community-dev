<?php

namespace Pim\Bundle\CatalogBundle\Event;

/**
 * Catalog family events
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class FamilyEvents
{
    /**
     * This event is thrown before a family gets removed.
     *
     * The event listener receives an
     * Akeneo\Component\StorageUtils\Event\RemoveEvent instance.
     *
     * @staticvar string
     */
    const PRE_REMOVE = 'pim_catalog.pre_remove.family';

    /**
     * This event is thrown after a family gets removed.
     *
     * The event listener receives an
     * Akeneo\Component\StorageUtils\Event\RemoveEvent instance.
     *
     * @staticvar string
     */
    const POST_REMOVE = 'pim_catalog.post_remove.family';

    /**
     * This event is thrown before a family is saved.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_SAVE = 'pim_catalog.pre_save.family';

    /**
     * This event is thrown after a family has been saved.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const POST_SAVE = 'pim_catalog.post_save.family';

    /**
     * This event is thrown before several families get saved.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_SAVE_ALL = 'pim_catalog.pre_save_all.family';

    /**
     * This event is thrown after several families have been saved.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const POST_SAVE_ALL = 'pim_catalog.post_save_all.family';
}
