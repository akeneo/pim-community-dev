<?php

namespace Akeneo\Tool\Bundle\MeasureBundle\Manager;

/**
 * Measure manager
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class MeasureManager
{
    /**
     * @var array $config
     */
    protected $config = [];

    /**
     * Set measure config
     *
     * @param array $config
     */
    public function setMeasureConfig(array $config)
    {
        $this->config = $config;
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
            function ($unit) {
                return $unit['symbol'];
            },
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
    public function unitSymbolExistsInFamily($unitSymbol, $family)
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
    public function unitCodeExistsInFamily($unitCode, $family)
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
     * Get the family config
     *
     * @param string $family
     *
     * @throws \InvalidArgumentException
     * @return array
     */
    protected function getFamilyConfig($family)
    {
        if (!isset($this->config[$family])) {
            throw new \InvalidArgumentException(
                sprintf('Undefined measure family "%s"', $family)
            );
        }

        return $this->config[$family];
    }
}
