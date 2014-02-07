<?php
namespace Akeneo\Bundle\MeasureBundle\Family;

/**
 * Temperature measures constants
 *
 *
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
