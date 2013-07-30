<?php

namespace Oro\Bundle\MeasureBundle\Manager;

class MeasureManager
{
    protected $config = array();

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
        if (!isset($this->config[$family])) {
            throw new \InvalidArgumentException(sprintf(
                'Undefined measure family "%s"', $family
            ));
        }

        return array_map(function ($unit) {
            return $unit['symbol'];
        }, $this->config[$family]['units']);
    }
}
