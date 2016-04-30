<?php

namespace Akeneo\Bundle\MeasureBundle\Family;

/**
 * Temperature measures constants
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface TemperatureFamilyInterface
{
    /**
     * Family measure name
     * @staticvar string
     */
    const FAMILY = 'Temperature';

    /**
     * @staticvar string
     */
    const CELSIUS    = 'CELSIUS';

    /**
     * @staticvar string
     */
    const FAHRENHEIT = 'FAHRENHEIT';

    /**
     * @staticvar string
     */
    const KELVIN     = 'KELVIN';

    /**
     * @staticvar string
     */
    const RANKINE    = 'RANKINE';

    /**
     * @staticvar string
     */
    const REAUMUR    = 'REAUMUR';
}
