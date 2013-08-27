<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Validator\Constraints;

use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Pim\Bundle\ProductBundle\Validator\Constraints\ValidMetric;
use Pim\Bundle\ProductBundle\Validator\Constraints\ValidMetricValidator;

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

    /**
     * @var array Measures
     */
    protected $measures = array(
        'measures_config' => array(
            'Length' => array(
                'standard' => 'METER',
                'units' => array(
                    'INCH' => array(),
                    'KILOMETER' => array(),
                    'METER' => array(),
                )
            ),
            'Temperature' => array(
                'standard' => 'KELVIN',
                'units' => array(
                    'CELCIUS' => array(),
                    'KELVIN' => array(),
                    'RANKINE' => array(),
                    'REAUMUR' => array()
                )
            )
        )
    );

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $this->validator = new ValidMetricValidator($this->measures);
        $this->validator->initialize($this->context);
        $this->constraint = new ValidMetric();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        $this->context = null;
        $this->validator = null;
        $this->constraint = null;

        parent::tearDown();
    }

    /**
     * Create a product attribute entity
     *
     * @param string $metricFamily
     * @param string $metricUnit
     *
     * @return ProductAttribute
     */
    protected function createProductAttribute($metricFamily, $metricUnit = '')
    {
        $attribute = new ProductAttribute();

        $attribute->setAttributeType('pim_product_metric');
        $attribute->setMetricFamily($metricFamily);
        $attribute->setDefaultMetricUnit($metricUnit);

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
        $attribute = $this->createProductAttribute($metricFamily);

        $this->context->expects($this->once())
            ->method('addViolationAt')
            ->with('metricFamily', $this->constraint->familyMessage);

        $this->validator->validate($attribute, $this->constraint);
    }

    /**
     * Provider for metric type violation
     *
     * @return array
     *
     * @static
     */
    public static function providerMetricFamilyInvalid()
    {
        return array(
            array('METER'),
            array('CELCIUS'),
            array('invalid_family')
        );
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
        $attribute = $this->createProductAttribute($metricFamily, $metricUnit);

        $this->context->expects($this->once())
            ->method('addViolationAt')
            ->with('defaultMetricUnit', $this->constraint->unitMessage);

        $this->validator->validate($attribute, $this->constraint);
    }

    /**
     * Provider for metric unit violation
     *
     * @return array
     *
     * @static
     */
    public static function providerMetricUnitInvalid()
    {
        return array(
            array('Length', 'REAUMUR'),
            array('Temperature', 'KILOMETER'),
            array('Temperature','invalid_unit')
        );
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
        $attribute = $this->createProductAttribute($metricFamily, $metricUnit);

        $this->context->expects($this->never())
            ->method('addViolationAt');

        $this->validator->validate($attribute, $this->constraint);
    }

    /**
     * Provider for metric unit violation
     *
     * @return array
     *
     * @static
     */
    public static function providerMetricFamilyAndUnitValid()
    {
        return array(
            array('Length', 'INCH'),
            array('Temperature', 'RANKINE'),
            array('Temperature','KELVIN')
        );
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
