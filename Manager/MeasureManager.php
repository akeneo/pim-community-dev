<?php

namespace Akeneo\Bundle\MeasureBundle\Manager;

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
    protected $config = array();

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
        $unitsConfig =  $familyConfig['units'];

        return array_map(
            function ($unit) {
                return $unit['symbol'];
            },
            $unitsConfig
        );
    }

    /**
     * Check if unit exists in the given family
     *
     * @param string $unit   the unit to check
     * @param string $family the measure family
     *
     * @return bool
     */
    public function unitExistsInFamily($unit, $family)
    {
        return in_array($unit, $this->getUnitSymbolsForFamily($family));
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
     * Get the family config
     *
     * @param string $family
     *
     * @return array
     * @throws \InvalidArgumentException
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
