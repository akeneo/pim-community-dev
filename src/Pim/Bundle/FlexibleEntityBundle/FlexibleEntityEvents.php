<?php

namespace Pim\Bundle\FlexibleEntityBundle;

/**
 * Flexible events
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
