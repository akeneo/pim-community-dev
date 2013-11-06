<?php
namespace Pim\Bundle\FlexibleEntityBundle;

/**
 * Flexible events
 *
 *
 */
final class FlexibleEntityEvents
{

    /**
     * This event is thrown each time an attribute is created in the system.
     *
     * The event listener receives an
     * Pim\Bundle\FlexibleEntityBundle\Event\FilterFlexibleEvent instance.
     *
     * @var string
     */
    const CREATE_ATTRIBUTE = 'pim_flexible.create_attribute';

    /**
     * This event is thrown each time a flexible is created in the system.
     *
     * The event listener receives an
     * Pim\Bundle\FlexibleEntityBundle\Event\FilterFlexibleEvent instance.
     *
     * @var string
     */
    const CREATE_FLEXIBLE  = 'pim_flexible.create_flexible';

    /**
     * This event is thrown each time a value is created in the system.
     *
     * The event listener receives an
     * Pim\Bundle\FlexibleEntityBundle\Event\FilterFlexibleEvent instance.
     *
     * @var string
     */
    const CREATE_VALUE     = 'pim_flexible.create_value';
}
