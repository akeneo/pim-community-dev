<?php

namespace Pim\Bundle\EnrichBundle\Event;

/**
 * Attribute group events
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeGroupEvents
{
    /**
     * This event is dispatched each time an attribute group has been created.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance.
     *
     * @staticvar string
     */
    const POST_CREATE = 'pim_enrich.attribute_group.post_create';
}
