<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\Family;

/**
 * Pressure measures constants
 *
 * @author    Grégory Planchat <gregory@luni.fr>
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface PressureFamilyInterface
{
    /**
     * Family measure name
     * @staticvar string
     */
    const FAMILY = 'Pressure';

    /**
     * @staticvar string
     */
    const PASCAL = 'PASCAL';

    /**
     * @staticvar string
     */
    const HECTOPASCAL = 'HECTOPASCAL';

    /**
     * @staticvar string
     */
    const MMHG = 'MMHG';

    /**
     * @staticvar string
     */
    const ATM = 'ATM';

    /**
     * @staticvar string
     */
    const BAR = 'BAR';

    /**
     * @staticvar string
     */
    const MILLIBAR = 'MILLIBAR';

    /**
     * @staticvar string
     */
    const TORR = 'TORR';
}
