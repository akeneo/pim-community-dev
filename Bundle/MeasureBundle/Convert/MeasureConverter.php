<?php
namespace Oro\Bundle\MeasureBundle\Convert;

use Oro\Bundle\MeasureBundle\Exception\UnknownFamilyMeasureException;
use Oro\Bundle\MeasureBundle\Exception\UnknownMeasureException;
use Oro\Bundle\MeasureBundle\Exception\UnknownOperatorException;

/**
 * Aims to convert measures
 */
class MeasureConverter
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $family;

    /**
     * Constructor
     *
     * @param array $config Configuration parameters
     */
    public function __construct($config = array())
    {
        $this->config = $config['measures_config'];
    }

    /**
     * Set a family for the converter
     * @param string $family
     *
     * @return MeasureConverter
     *
     * @throws Oro\Bundle\MeasureBundle\Exception\UnknownFamilyMeasureException
     */
    public function setFamily($family)
    {
        if (!isset($this->config[$family])) {
            throw new UnknownFamilyMeasureException();
        }

        $this->family = $family;

        return $this;
    }

    /**
     * Convert a value from a base measure to a final measure
     * @param string $baseUnit  Base unit for value
     * @param string $finalUnit Result unit for value
     * @param double $value     Value to convert
     *
     * @return double
     */
    public function convert($baseUnit, $finalUnit, $value)
    {
        $standardValue = $this->convertBaseToStandard($baseUnit, $value);

        $result = $this->convertStandardToResult($finalUnit, $standardValue);

        return $result;
    }

    /**
     * Convert a value in a base unit to the standard unit
     * @param string $baseUnit Base unit for value
     * @param double $value    Value to convert
     *
     * @return float
     *
     * @throws Oro\Bundle\MeasureBundle\Exception\UnknownOperatorException
     * @throws Oro\Bundle\MeasureBundle\Exception\UnknownMeasureException
     */
    public function convertBaseToStandard($baseUnit, $value)
    {
        if (!isset($this->config[$this->family]['units'][$baseUnit])) {
            throw new UnknownMeasureException();
        }
        $conversionConfig = $this->config[$this->family]['units'][$baseUnit]['convert'];
        $convertedValue = $value;

        // calculate result with conversion config
        foreach ($conversionConfig as $operation) {
            foreach ($operation as $operator => $operand) {
                switch ($operator) {
                    case "div":
                        if ($operand !== 0) {
                            $convertedValue = $convertedValue / $operand;
                        }
                        break;
                    case "mul":
                        $convertedValue = $convertedValue * $operand;
                        break;
                    case "add":
                        $convertedValue = $convertedValue + $operand;
                        break;
                    case "sub":
                        $convertedValue = $convertedValue - $operand;
                        break;
                    default:
                        throw new UnknownOperatorException();
                }
            }
        }

        return $convertedValue;
    }

    /**
     * Convert a value in a standard unit to a final unit
     * @param string $finalUnit Final unit for value
     * @param double $value     Value to convert
     *
     * @return double
     *
     * @throws Oro\Bundle\MeasureBundle\Exception\UnknownOperatorException
     * @throws Oro\Bundle\MeasureBundle\Exception\UnknownMeasureException
     */
    public function convertStandardToResult($finalUnit, $value)
    {
        if (!isset($this->config[$this->family]['units'][$finalUnit])) {
            throw new UnknownMeasureException();
        }
        $conversionConfig = $this->config[$this->family]['units'][$finalUnit]['convert'];
        $convertedValue = $value;

        // calculate result with conversion config (calculs must be reversed and operation inversed)
        foreach (array_reverse($conversionConfig) as $operation) {
            foreach ($operation as $operator => $operand) {
                switch ($operator) {
                    case "div":
                        $convertedValue = $convertedValue * $operand;
                        break;
                    case "mul":
                        if ($operand !== 0) {
                            $convertedValue = $convertedValue / $operand;
                        }
                        break;
                    case "add":
                        $convertedValue = $convertedValue - $operand;
                        break;
                    case "sub":
                        $convertedValue = $convertedValue + $operand;
                        break;
                    default:
                        throw new UnknownOperatorException();
                }
            }
        }

        return $convertedValue;
    }
}
