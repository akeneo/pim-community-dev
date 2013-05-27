<?php
namespace Oro\Bundle\FlexibleEntityBundle;

/**
 * Flexible events
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
final class FlexibleEntityEvents
{

    /**
     * This event is thrown each time an attribute is created in the system.
     *
     * The event listener receives an
     * Oro\Bundle\FlexibleEntityBundle\Event\FilterFlexibleEvent instance.
     *
     * @var string
     */
    const CREATE_ATTRIBUTE = 'oro_flexible.create_attribute';

    /**
     * This event is thrown each time a flexible is created in the system.
     *
     * The event listener receives an
     * Oro\Bundle\FlexibleEntityBundle\Event\FilterFlexibleEvent instance.
     *
     * @var string
     */
    const CREATE_FLEXIBLE  = 'oro_flexible.create_flexible';

    /**
     * This event is thrown each time a value is created in the system.
     *
     * The event listener receives an
     * Oro\Bundle\FlexibleEntityBundle\Event\FilterFlexibleEvent instance.
     *
     * @var string
     */
    const CREATE_VALUE     = 'oro_flexible.create_value';
}
