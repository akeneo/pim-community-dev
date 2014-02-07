<?php
namespace Akeneo\Bundle\MeasureBundle\Family;

/**
 * Binary measures constants
 *
 *
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
    const BIT      = 'BIT';

    /**
     * @staticvar string
     */
    const BYTE     = 'BYTE';

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
