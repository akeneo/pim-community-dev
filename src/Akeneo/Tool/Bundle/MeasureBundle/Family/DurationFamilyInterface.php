<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\Family;

/**
 * Duration constants
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface DurationFamilyInterface
{
    /**
     * Family measure name
     * @staticvar string
     */
    const FAMILY = 'Duration';
    
    /**
     * @staticvar string
     */
    const MILLISECOND = 'MILLISECOND';

    /**
     * @staticvar string
     */
    const SECOND = 'SECOND';

    /**
     * @staticvar string
     */
    const MINUTE = 'MINUTE';

    /**
     * @staticvar string
     */
    const HOUR = 'HOUR';

    /**
     * @staticvar string
     */
    const DAY = 'DAY';

    /**
     * @staticvar string
     */
    const WEEK = 'WEEK';

    /**
     * @staticvar string
     */
    const MONTH = 'MONTH';

    /**
     * @staticvar string
     */
    const YEAR = 'YEAR';
}
