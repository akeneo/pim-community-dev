<?php

namespace Pim\Component\Catalog\Event;

/**
 * Catalog family events
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FamilyEvents
{
    /**
     * This event is dispatched each time an family has been updated
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const POST_UPDATE = 'pim_catalog.post_update.family';
}
