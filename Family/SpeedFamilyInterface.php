<?php
namespace Akeneo\Bundle\MeasureBundle\Family;

/**
 * Speed measures constants
 *
 *
 */
interface SpeedFamilyInterface
{

    /**
     * Family measure name
     * @staticvar string
     */
    const FAMILY = 'Speed';

    /**
     * @staticvar string
     */
    const FOOT_PER_SECOND    = 'FOOT_PER_SECOND';

    /**
     * @staticvar string
     */
    const FOOT_PER_HOUR      = 'FOOT_PER_HOUR';

    /**
     * @staticvar string
     */
    const KILOMETER_PER_HOUR = 'KILOMETER_PER_HOUR';

    /**
     * @staticvar string
     */
    const METER_PER_HOUR     = 'METER_PER_HOUR';

    /**
     * @staticvar string
     */
    const METER_PER_MINUTE   = 'METER_PER_MINUTE';

    /**
     * @staticvar string
     */
    const METER_PER_SECOND   = 'METER_PER_SECOND';

    /**
     * @staticvar string
     */
    const MILE_PER_HOUR      = 'MILE_PER_HOUR';

    /**
     * @staticvar string
     */
    const YARD_PER_HOUR      = 'YARD_PER_HOUR';
}
