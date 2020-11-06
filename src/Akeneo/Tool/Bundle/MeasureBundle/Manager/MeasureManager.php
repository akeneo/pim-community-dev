<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\Manager;

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
    /** @var LegacyMeasurementProvider */
    private $legacyMeasurementProvider;

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
    public function getUnitSymbolsForFamily(string $family): array
    {
        $familyConfig = $this->getFamilyConfig($family);
        $unitsConfig = $familyConfig['units'];

        return array_map(
            fn($unit) => $unit['symbol'],
            $unitsConfig
        );
    }

    /**
     * Check if the unit symbol (like 'g' for GRAM) exists for the given family
     *
     * @param string $unitSymbol the unit symbol to check
     * @param string $family     the measure family
     */
    public function unitSymbolExistsInFamily(string $unitSymbol, string $family): bool
    {
        return in_array($unitSymbol, $this->getUnitSymbolsForFamily($family));
    }

    /**
     * Check if the unit code (like 'GRAM' or 'KILOMETER') exists for the given family
     *
     * @param string $unitCode the unit code to check
     * @param string $family   the measure family
     */
    public function unitCodeExistsInFamily(string $unitCode, string $family): bool
    {
        return in_array($unitCode, $this->getUnitCodesForFamily($family));
    }

    /**
     * Get standard unit for a measure family
     *
     * @param string $family
     */
    public function getStandardUnitForFamily(string $family): string
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
    public function getUnitCodesForFamily(string $family): array
    {
        $familyConfig = $this->getFamilyConfig($family);

        return array_keys($familyConfig['units']);
    }

    /**
     * Get the family config
     *
     * @param string $family
     *
     * @throws \InvalidArgumentException
     */
    protected function getFamilyConfig(string $family): array
    {
        $families = $this->legacyMeasurementProvider->getMeasurementFamilies();
        if (!isset($families[$family])) {
            throw new \InvalidArgumentException(
                sprintf('Undefined measure family "%s"', $family)
            );
        }

        return $families[$family];
    }
}
