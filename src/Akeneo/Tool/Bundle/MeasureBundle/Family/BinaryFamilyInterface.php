<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\Family;

/**
 * Binary measures constants
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface BinaryFamilyInterface
{
    /**
     * Family measure name
     * @staticvar string
     */
    const FAMILY = 'Binary';

    /**
     * @staticvar string
     */
    const BIT = 'BIT';

    /**
     * @staticvar string
     */
    const BYTE = 'BYTE';

    /**
     * @staticvar string
     */
    const KILOBYTE = 'KILOBYTE';

    /**
     * @staticvar string
     */
    const MEGABYTE = 'MEGABYTE';

    /**
     * @staticvar string
     */
    const GIGABYTE = 'GIGABYTE';

    /**
     * @staticvar string
     */
    const TERABYTE = 'TERABYTE';
}
