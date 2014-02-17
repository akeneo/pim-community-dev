<?php

namespace Akeneo\Bundle\MeasureBundle\Tests\Unit\Convert;

use Akeneo\Bundle\MeasureBundle\Family\WeightFamilyInterface;
use Akeneo\Bundle\MeasureBundle\Family\VolumeFamilyInterface;
use Akeneo\Bundle\MeasureBundle\Family\TemperatureFamilyInterface;
use Akeneo\Bundle\MeasureBundle\Family\SpeedFamilyInterface;
use Akeneo\Bundle\MeasureBundle\Family\PowerFamilyInterface;
use Akeneo\Bundle\MeasureBundle\Family\FrequencyFamilyInterface;
use Akeneo\Bundle\MeasureBundle\Family\BinaryFamilyInterface;
use Akeneo\Bundle\MeasureBundle\Family\AreaFamilyInterface;
use Akeneo\Bundle\MeasureBundle\Family\LengthFamilyInterface;
use Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Bundle\MeasureBundle\Exception\UnknownFamilyMeasureException;
use Akeneo\Bundle\MeasureBundle\Exception\UnknownMeasureException;
use Akeneo\Bundle\MeasureBundle\Exception\UnknownOperatorException;

use Symfony\Component\Yaml\Yaml;

/**
 * Test related class
 */
class MeasureConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Converter service
     * @var MeasureConverter
     */
    protected $converter;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $configFile = realpath(dirname(__FILE__) .'/../../../Resources/config/measure.yml');
        $config = $this->initializeConfig($configFile);

        $this->converter = $this->initializeConverter($config);
    }

    /**
     * Initialize a configuration
     * @param string $filepath
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function initializeConfig($filepath)
    {
        if (!file_exists($filepath)) {
            throw new \Exception("Config file $filepath not exists");
        }

        return Yaml::parse($filepath);
    }

    /**
     * Initialize converter
     * @param array $config
     *
     * @throws \Exception
     *
     * @return \Akeneo\Bundle\MeasureBundle\Convert\MeasureConverter
     */
    protected function initializeConverter($config)
    {
        return new MeasureConverter($config);
    }

    /**
     * Test converter with data in provider method
     * @param string $family        MeasureFamily for conversion
     * @param string $baseUnit      Base unit used by base value
     * @param string $convertedUnit Converted unit asked
     * @param number $baseValue     Base value
     * @param number $expectedValue Expected value after conversion
     *
     * @dataProvider provider
     */
    public function testConvert($family, $baseUnit, $convertedUnit, $baseValue, $expectedValue)
    {
        $this->converter->setFamily($family);

        $convertedValue = $this->converter->convert($baseUnit, $convertedUnit, $baseValue);
        $this->assertEquals($expectedValue, $convertedValue);

        $resultValue = $this->converter->convert($convertedUnit, $baseUnit, $convertedValue);
        $this->assertEquals($baseValue, $resultValue);
    }

    /**
     * Data provider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @return array
     *
     * @static
     */
    public static function provider()
    {
        return array(
            // Area tests
            array(
                AreaFamilyInterface::FAMILY, AreaFamilyInterface::SQUARE_METER, AreaFamilyInterface::HECTARE, 200, 0.02
            ),
            array(
                AreaFamilyInterface::FAMILY,
                AreaFamilyInterface::SQUARE_INCH,
                AreaFamilyInterface::SQUARE_MIL,
                10,
                10000000
            ),
            array(
                AreaFamilyInterface::FAMILY,
                AreaFamilyInterface::SQUARE_FURLONG,
                AreaFamilyInterface::SQUARE_MILE,
                100,
                1.5625062462063
                ),
            // Binary tests
            array(
                BinaryFamilyInterface::FAMILY, BinaryFamilyInterface::BYTE, BinaryFamilyInterface::KILOBYTE, 1024, 1
            ),
            array(
                BinaryFamilyInterface::FAMILY,
                BinaryFamilyInterface::GIGABYTE,
                BinaryFamilyInterface::BIT,
                2,
                17179869184
            ),
            // Frequency tests
            array(
                FrequencyFamilyInterface::FAMILY,
                FrequencyFamilyInterface::HERTZ,
                FrequencyFamilyInterface::KILOHERTZ,
                10000,
                10
            ),
            array(
                FrequencyFamilyInterface::FAMILY,
                FrequencyFamilyInterface::TERAHERTZ,
                FrequencyFamilyInterface::MEGAHERTZ,
                2,
                2000000
            ),
            // Length tests
            array(
                LengthFamilyInterface::FAMILY,
                LengthFamilyInterface::MILLIMETER,
                LengthFamilyInterface::CENTIMETER,
                50,
                5
            ),
            array(
                LengthFamilyInterface::FAMILY,
                LengthFamilyInterface::INCH,
                LengthFamilyInterface::MILE,
                15000,
                0.23674242424242
            ),
            array(
                LengthFamilyInterface::FAMILY,
                LengthFamilyInterface::KILOMETER,
                LengthFamilyInterface::YARD,
                10,
                10936.132983377
            ),
            // Power tests
            array(
                PowerFamilyInterface::FAMILY, PowerFamilyInterface::WATT, PowerFamilyInterface::KILOWATT, 10000, 10
            ),
            array(
                PowerFamilyInterface::FAMILY, PowerFamilyInterface::TERAWATT, PowerFamilyInterface::MEGAWATT, 2, 2000000
            ),
            array(
                PowerFamilyInterface::FAMILY, PowerFamilyInterface::GIGAWATT, PowerFamilyInterface::TERAWATT, 5, 0.005
            ),
            // Speed tests
            array(
                SpeedFamilyInterface::FAMILY,
                SpeedFamilyInterface::KILOMETER_PER_HOUR,
                SpeedFamilyInterface::METER_PER_SECOND,
                36,
                10
            ),
            array(
                SpeedFamilyInterface::FAMILY,
                SpeedFamilyInterface::FOOT_PER_SECOND,
                SpeedFamilyInterface::MILE_PER_HOUR,
                17,
                11.590909090909
            ),
            array(
                SpeedFamilyInterface::FAMILY,
                SpeedFamilyInterface::YARD_PER_HOUR,
                SpeedFamilyInterface::METER_PER_MINUTE,
                26,
                0.39624
            ),
            // Temperature tests
            array(
                TemperatureFamilyInterface::FAMILY,
                TemperatureFamilyInterface::CELSIUS,
                TemperatureFamilyInterface::FAHRENHEIT,
                20,
                68
            ),
            array(
                TemperatureFamilyInterface::FAMILY,
                TemperatureFamilyInterface::REAUMUR,
                TemperatureFamilyInterface::RANKINE,
                6,
                505.17
            ),
            array(
                TemperatureFamilyInterface::FAMILY,
                TemperatureFamilyInterface::FAHRENHEIT,
                TemperatureFamilyInterface::REAUMUR,
                26720.33,
                11861.48
            ),
            // Volume tests
            array(
                VolumeFamilyInterface::FAMILY, VolumeFamilyInterface::CUBIC_METER, VolumeFamilyInterface::LITER, 5, 5000
            ),
            array(
                VolumeFamilyInterface::FAMILY,
                VolumeFamilyInterface::MILLILITER,
                VolumeFamilyInterface::PINT,
                568.26125,
                1
            ),
            array(
                VolumeFamilyInterface::FAMILY,
                VolumeFamilyInterface::CUBIC_INCH,
                VolumeFamilyInterface::OUNCE,
                12,
                6.9209283170784
            ),
            // Weight tests
            array(
                WeightFamilyInterface::FAMILY,
                WeightFamilyInterface::GRAM,
                WeightFamilyInterface::OUNCE,
                3059,
                107.90304960377
            ),
            array(
                WeightFamilyInterface::FAMILY,
                WeightFamilyInterface::POUND,
                WeightFamilyInterface::KILOGRAM,
                2,
                0.90718474
            ),
            array(
                WeightFamilyInterface::FAMILY,
                WeightFamilyInterface::GRAIN,
                WeightFamilyInterface::MARC,
                500,
                0.13237775280899
            ),
        );
    }

    /**
     * Test related exception
     * @expectedException Akeneo\Bundle\MeasureBundle\Exception\UnknownFamilyMeasureException
     */
    public function testUnknownFamilyMeasureException()
    {
        $this->converter->setFamily('test-family');
    }

    /**
     * Test related exception in base to standard method
     * @expectedException Akeneo\Bundle\MeasureBundle\Exception\UnknownMeasureException
     */
    public function testUnknownMeasureExceptionBaseToStandard()
    {
        $this->converter->setFamily(WeightFamilyInterface::FAMILY);
        $this->converter->convert('test-unit', WeightFamilyInterface::GRAM, 50);
    }

    /**
     * Test related exception in standard to final method
     * @expectedException Akeneo\Bundle\MeasureBundle\Exception\UnknownMeasureException
     */
    public function testUnknownMeasureExceptionStandardToFinal()
    {
        $this->converter->setFamily(WeightFamilyInterface::FAMILY);
        $this->converter->convert(WeightFamilyInterface::GRAM, 'test-unit', 50);
    }

    /**
     * Test related exception in base to standard method
     * @expectedException Akeneo\Bundle\MeasureBundle\Exception\UnknownOperatorException
     */
    public function testUnknownOperatorExceptionBaseToStandard()
    {
        $configFile = dirname(__FILE__) .'/../Files/measure-test.yml';
        $config = $this->initializeConfig($configFile);

        $converter = $this->initializeConverter($config);
        $converter->setFamily(LengthFamilyInterface::FAMILY);
        $converter->convert(LengthFamilyInterface::METER, LengthFamilyInterface::CENTIMETER, 100);
    }

    /**
     * Test related exception in standard to final method
     * @expectedException Akeneo\Bundle\MeasureBundle\Exception\UnknownOperatorException
     */
    public function testUnknownOperatorExceptionStandardToFinal()
    {
        $configFile = dirname(__FILE__) .'/../Files/measure-test.yml';
        $config = $this->initializeConfig($configFile);

        $converter = $this->initializeConverter($config);
        $converter->setFamily(LengthFamilyInterface::FAMILY);
        $converter->convert(LengthFamilyInterface::CENTIMETER, LengthFamilyInterface::METER, 100);
    }
}
