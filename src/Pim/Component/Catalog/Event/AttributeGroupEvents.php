<?php

namespace Pim\Component\Catalog\Event;

/**
 * Catalog attribute group events
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeGroupEvents
{
    /**
     * This event is dispatched each time an attribute group has been updated
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const POST_UPDATE = 'pim_catalog.post_update.attribute_group';
}
