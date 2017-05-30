<?php

namespace Akeneo\Bundle\MeasureBundle\Convert;

use Akeneo\Bundle\MeasureBundle\Exception\UnknownFamilyMeasureException;
use Akeneo\Bundle\MeasureBundle\Exception\UnknownMeasureException;
use Akeneo\Bundle\MeasureBundle\Exception\UnknownOperatorException;

/**
 * Aims to convert measures
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface MeasureConverterInterface
{
    /**
     * Set a family for the converter
     * @param string $family
     *
     * @throws UnknownFamilyMeasureException
     * @return MeasureConverterInterface
     *
     */
    public function setFamily($family);

    /**
     * Convert a value from a base measure to a final measure
     * @param string $baseUnit  Base unit for value
     * @param string $finalUnit Result unit for value
     * @param double $value     Value to convert
     *
     * @return double
     */
    public function convert($baseUnit, $finalUnit, $value);

    /**
     * Convert a value in a base unit to the standard unit
     * @param string $baseUnit Base unit for value
     * @param double $value    Value to convert
     *
     * @throws UnknownOperatorException
     * @throws UnknownMeasureException
     * @return double
     *
     */
    public function convertBaseToStandard($baseUnit, $value);

    /**
     * Convert a value in a standard unit to a final unit
     * @param string $finalUnit Final unit for value
     * @param double $value     Value to convert
     *
     * @throws UnknownOperatorException
     * @throws UnknownMeasureException
     * @return double
     *
     */
    public function convertStandardToResult($finalUnit, $value);
}
