<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\Family;

/**
 * Power measures constants
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface PowerFamilyInterface
{
    /**
     * Family measure name
     * @staticvar string
     */
    const FAMILY = 'Power';

    /**
     * @staticvar string
     */
    const GIGAWATT = 'GIGAWATT';

    /**
     * @staticvar string
     */
    const KILOWATT = 'KILOWATT';

    /**
     * @staticvar string
     */
    const MEGAWATT = 'MEGAWATT';

    /**
     * @staticvar string
     */
    const TERAWATT = 'TERAWATT';

    /**
     * @staticvar string
     */
    const WATT = 'WATT';
}
