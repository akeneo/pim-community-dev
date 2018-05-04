<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\Family;

/**
 * Voltage measures constants
 *
 * @author Emmanuel Valette <evalette@expertime.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/MIT MIT
 */
interface VoltageFamilyInterface
{
    /**
     * Family measure name
     * @staticvar string
     */
    const FAMILY = 'Voltage';

    /**
     * @staticvar string
     */
    const MILLIVOLT = 'MILLIVOLT';
    
    /**
     * @staticvar string
     */
    const CENTIVOLT = 'CENTIVOLT';

    /**
     * @staticvar string
     */
    const DECIVOLT = 'DECIVOLT';

    /**
     * @staticvar string
     */
    const VOLT = 'VOLT';
    
    /**
     * @staticvar string
     */
    const DEKAVOLT = 'DEKAVOLT';

    /**
     * @staticvar string
     */
    const HECTOVOLT = 'HECTOVOLT';

    /**
     * @staticvar string
     */
    const KILOVOLT = 'KILOVOLT';
}
