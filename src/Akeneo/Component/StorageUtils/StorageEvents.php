<?php

namespace Akeneo\Component\StorageUtils;

/**
 * Storage events.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class StorageEvents
{
    /**
     * This event is thrown before an object gets removed.
     *
     * The event listener receives an
     * Akeneo\Component\StorageUtils\Event\RemoveEvent instance.
     *
     * @staticvar string
     */
    const PRE_REMOVE = 'akeneo.storage.pre_remove';

    /**
     * This event is thrown after an object gets removed.
     *
     * The event listener receives an
     * Akeneo\Component\StorageUtils\Event\RemoveEvent instance.
     *
     * @staticvar string
     */
    const POST_REMOVE = 'akeneo.storage.post_remove';

    /**
     * This event is thrown before an object gets saved.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_SAVE = 'akeneo.storage.pre_save';

    /**
     * This event is thrown after an object gets saved.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const POST_SAVE = 'akeneo.storage.post_save';

    /**
     * This event is thrown before several objects get saved.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_SAVE_ALL = 'akeneo.storage.pre_save_all';

    /**
     * This event is thrown after several objects have been saved.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const POST_SAVE_ALL = 'akeneo.storage.post_save_all';
}
