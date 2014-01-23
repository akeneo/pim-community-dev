<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Validator\Constraints\ValidMetric;
use Pim\Bundle\CatalogBundle\Validator\Constraints\ValidMetricValidator;

/**
 * Test related class
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidMetricValidatorTest extends \PHPUnit_Framework_TestCase
{
    protected $context;
    protected $validator;
    protected $constraint;
    protected $propertyAccessor;

    /**
     * @var array Measures
     */
    protected $measures = [
        'measures_config' => [
            'Length' => [
                'standard' => 'METER',
                'units' => [
                    'INCH' => [],
                    'KILOMETER' => [],
                    'METER' => [],
                ]
            ],
            'Temperature' => [
                'standard' => 'KELVIN',
                'units' => [
                    'CELSIUS' => [],
                    'KELVIN' => [],
                    'RANKINE' => [],
                    'REAUMUR' => []
                ]
            ]
        ]
    ];

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->propertyAccessor = $this->getMock('Symfony\Component\PropertyAccess\PropertyAccessorInterface');
        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContext', [], [], '', false);
        $this->validator = new ValidMetricValidator($this->propertyAccessor, $this->measures);
        $this->validator->initialize($this->context);
        $this->constraint = new ValidMetric();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $this->context = null;
        $this->validator = null;
        $this->constraint = null;

        parent::tearDown();
    }

    /**
     * Create an attribute entity
     *
     * @param string $metricFamily
     * @param string $defaultMetricUnit
     *
     * @return Attribute
     */
    protected function createAttribute($metricFamily, $defaultMetricUnit = '')
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Model\AttributeInterface');

        $this->propertyAccessor->expects($this->any())
            ->method('getValue')
            ->will(
                $this->returnCallback(
                    function ($object, $property) use ($attribute, $metricFamily, $defaultMetricUnit) {
                        $this->assertSame($attribute, $object);

                        return $$property;
                    }
                )
            );

        return $attribute;
    }

    /**
     * Test case with invalid metric family
     *
     * @param string $metricFamily
     *
     * @dataProvider providerMetricFamilyInvalid
     */
    public function testMetricFamilyInvalid($metricFamily)
    {
        $attribute = $this->createAttribute($metricFamily);

        $this->context->expects($this->once())
            ->method('addViolationAt')
            ->with('metricFamily', $this->constraint->familyMessage);

        $this->validator->validate($attribute, $this->constraint);
    }

    /**
     * Provider for metric type violation
     *
     * @return array
     */
    public static function providerMetricFamilyInvalid()
    {
        return [
            ['METER'],
            ['CELSIUS'],
            ['invalid_family']
        ];
    }

    /**
     * Test case with invalid metric unit
     *
     * @param string $metricFamily
     * @param string $metricUnit
     *
     * @dataProvider providerMetricUnitInvalid
     */
    public function testMetricUnitInvalid($metricFamily, $metricUnit)
    {
        $attribute = $this->createAttribute($metricFamily, $metricUnit);

        $this->context->expects($this->once())
            ->method('addViolationAt')
            ->with('defaultMetricUnit', $this->constraint->unitMessage);

        $this->validator->validate($attribute, $this->constraint);
    }

    /**
     * Provider for metric unit violation
     *
     * @return array
     */
    public static function providerMetricUnitInvalid()
    {
        return [
            ['Length', 'REAUMUR'],
            ['Temperature', 'KILOMETER'],
            ['Temperature', 'invalid_unit']
        ];
    }

    /**
     * Test case with valid metric type and unit
     *
     * @param string $metricFamily
     * @param string $metricUnit
     *
     * @dataProvider providerMetricFamilyAndUnitValid
     */
    public function testMetricFamilyAndUnitValid($metricFamily, $metricUnit)
    {
        $attribute = $this->createAttribute($metricFamily, $metricUnit);

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->validate($attribute, $this->constraint);
    }

    /**
     * Provider for metric unit violation
     *
     * @return array
     */
    public static function providerMetricFamilyAndUnitValid()
    {
        return [
            ['Length', 'INCH'],
            ['Temperature', 'RANKINE'],
            ['Temperature', 'KELVIN']
        ];
    }

    /**
     * Test validatedBy method
     */
    public function testValidatedBy()
    {
        $this->assertEquals($this->constraint->validatedBy(), 'pim_metric_validator');
    }

    /**
     * Test getTargets method
     */
    public function testGetTargets()
    {
        $this->assertEquals($this->constraint->getTargets(), 'class');
    }
}
