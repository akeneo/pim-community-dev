<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\Family;

/**
 * Resistence family constants
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ResistanceFamilyInterface
{
    /**
     * Family measure name
     * @staticvar string
     */
    const FAMILY = 'Resistance';
    
    /**
     * @staticvar string
     */
    const MILLIOHM = 'MILLIOHM';

    /**
     * @staticvar string
     */
    const CENTIOHM = 'CENTIOHM';

    /**
     * @staticvar string
     */
    const DECIOHM = 'DECIOHM';

    /**
     * @staticvar string
     */
    const OHM = 'OHM';

    /**
     * @staticvar string
     */
    const DEKAOHM = 'DEKAOHM';

    /**
     * @staticvar string
     */
    const HECTOHM = 'HECTOHM';

    /**
     * @staticvar string
     */
    const KILOHM = 'KILOHM';

    /**
     * @staticvar string
     */
    const MEGOHM = 'MEGOHM';
}
