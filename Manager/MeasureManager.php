<?php

namespace Akeneo\Bundle\MeasureBundle\Manager;

/**
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
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
        return array_map(
            static function ($unit) {
                return $unit['symbol'];
            },
            $this->getFamilyConfig($family)['units']
        );
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
        return $this->getFamilyConfig($family)['standard'];
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
