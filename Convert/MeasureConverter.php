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
     * @throws UnknownFamilyMeasureException
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
     * @return double
     *
     * @throws UnknownOperatorException
     * @throws UnknownMeasureException
     */
    public function convertBaseToStandard($baseUnit, $value)
    {
        if (!isset($this->config[$this->family]['units'][$baseUnit])) {
            throw new UnknownMeasureException(
                sprintf(
                    'Could not find metric unit "%s" in family "%s"',
                    $baseUnit,
                    $this->family
                )
            );
        }
        $conversionConfig = $this->config[$this->family]['units'][$baseUnit]['convert'];
        $convertedValue = $value;

        foreach ($conversionConfig as $operation) {
            foreach ($operation as $operator => $operand) {
                $convertedValue = $this->applyOperation($convertedValue, $operator, $operand);
            }
        }

        return $convertedValue;
    }

    /**
     * Apply operation between value and operand by using operator
     *
     * @param double $value    Value to convert
     * @param string $operator Operator to apply
     * @param double $operand  Operand to use
     *
     * @return double
     */
    protected function applyOperation($value, $operator, $operand)
    {
        $processedValue = $value;

        switch ($operator) {
            case "div":
                if ($operand !== 0) {
                    $processedValue = $processedValue / $operand;
                }
                break;
            case "mul":
                $processedValue = $processedValue * $operand;
                break;
            case "add":
                $processedValue = $processedValue + $operand;
                break;
            case "sub":
                $processedValue = $processedValue - $operand;
                break;
            default:
                throw new UnknownOperatorException();
        }

        return $processedValue;
    }

    /**
     * Convert a value in a standard unit to a final unit
     * @param string $finalUnit Final unit for value
     * @param double $value     Value to convert
     *
     * @return double
     *
     * @throws UnknownOperatorException
     * @throws UnknownMeasureException
     */
    public function convertStandardToResult($finalUnit, $value)
    {
        if (!isset($this->config[$this->family]['units'][$finalUnit])) {
            throw new UnknownMeasureException(
                sprintf(
                    'Could not find metric unit "%s" in family "%s"',
                    $finalUnit,
                    $this->family
                )
            );
        }
        $conversionConfig = $this->config[$this->family]['units'][$finalUnit]['convert'];
        $convertedValue = $value;

        // calculate result with conversion config (calculs must be reversed and operation inversed)
        foreach (array_reverse($conversionConfig) as $operation) {
            foreach ($operation as $operator => $operand) {
                $convertedValue = $this->applyReversedOperation($convertedValue, $operator, $operand);
            }
        }

        return $convertedValue;
    }

    /**
     * Apply reversed operation between value and operand by using operator
     *
     * @param double $value    Value to convert
     * @param string $operator Operator to apply
     * @param double $operand  Operand to use
     *
     * @return double
     */
    protected function applyReversedOperation($value, $operator, $operand)
    {
        $processedValue = $value;

        switch ($operator) {
            case "div":
                $processedValue = $processedValue * $operand;
                break;
            case "mul":
                if ($operand !== 0) {
                    $processedValue = $processedValue / $operand;
                }
                break;
            case "add":
                $processedValue = $processedValue - $operand;
                break;
            case "sub":
                $processedValue = $processedValue + $operand;
                break;
            default:
                throw new UnknownOperatorException();
        }

        return $processedValue;
    }
}
