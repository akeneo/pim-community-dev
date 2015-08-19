<?php

namespace Pim\Bundle\CatalogBundle\Event;

/**
 * Catalog channel events
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ChannelEvents
{
    /**
     * This event is thrown before a channel is removed.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_REMOVE = 'pim_catalog.pre_remove.channel';

    /**
     * This event is thrown before a channel is saved.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const PRE_SAVE = 'pim_catalog.pre_save.channel';

    /**
     * This event is thrown after a channel has been saved.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const POST_SAVE = 'pim_catalog.post_save.channel';
}
