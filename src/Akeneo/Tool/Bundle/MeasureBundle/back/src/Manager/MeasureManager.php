<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\Manager;

use Akeneo\Tool\Bundle\MeasureBundle\Exception\MeasurementFamilyNotFoundException;
use Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementProvider;

/**
 * Measure manager
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class MeasureManager
{
    private LegacyMeasurementProvider $legacyMeasurementProvider;

    public function __construct(LegacyMeasurementProvider $legacyMeasurementProvider)
    {
        $this->legacyMeasurementProvider = $legacyMeasurementProvider;
    }

    /**
     * Get unit symbols for a measure family
     *
     * @param string $family the measure family
     *
     * @return array the measure symbols
     */
    public function getUnitSymbolsForFamily($family)
    {
        $familyConfig = $this->getFamilyConfig($family);
        $unitsConfig = $familyConfig['units'];

        return array_map(
            static fn (array $unit) => $unit['symbol'],
            $unitsConfig
        );
    }

    /**
     * Check if the unit symbol (like 'g' for GRAM) exists for the given family
     *
     * @param string $unitSymbol the unit symbol to check
     * @param string $family     the measure family
     *
     * @return bool
     */
    public function unitSymbolExistsInFamily($unitSymbol, $family): bool
    {
        return in_array($unitSymbol, $this->getUnitSymbolsForFamily($family));
    }

    /**
     * Check if the unit code (like 'GRAM' or 'KILOMETER') exists for the given family
     *
     * @param string $unitCode the unit code to check
     * @param string $family   the measure family
     *
     * @return bool
     */
    public function unitCodeExistsInFamily($unitCode, $family): bool
    {
        return in_array($unitCode, $this->getUnitCodesForFamily($family));
    }

    /**
     * Get standard unit for a measure family
     *
     * @param string $family
     *
     * @return string
     */
    public function getStandardUnitForFamily($family)
    {
        $familyConfig = $this->getFamilyConfig($family);

        return $familyConfig['standard'];
    }

    /**
     * Get unit codes for a measure family
     *
     * @param string $family the measure family
     *
     * @return array the measure units code
     */
    public function getUnitCodesForFamily($family)
    {
        $familyConfig = $this->getFamilyConfig($family);

        return array_keys($familyConfig['units']);
    }

    /**
     * Check if provided family exists
     */
    public function familyExists(string $familyCode): bool
    {
        $families = $this->legacyMeasurementProvider->getMeasurementFamilies();

        return array_key_exists($familyCode, $families);
    }

    /**
     * Get the family config
     *
     * @param string $family
     *
     * @throws MeasurementFamilyNotFoundException
     * @return array
     */
    protected function getFamilyConfig($family)
    {
        $families = $this->legacyMeasurementProvider->getMeasurementFamilies();
        if (!isset($families[$family])) {
            throw new MeasurementFamilyNotFoundException(
                sprintf('Undefined measure family "%s"', $family)
            );
        }

        return $families[$family];
    }
}
