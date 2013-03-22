<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Validator;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;

use Pim\Bundle\ProductBundle\Validator\Constraints\ValidMetricAttribute;
use Pim\Bundle\ProductBundle\Validator\Constraints\ValidMetricAttributeValidator;

use Symfony\Component\Validator\GlobalExecutionContext;
use Symfony\Component\Validator\ExecutionContext;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test related class
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ValidMetricAttributeValidatorTest extends WebTestCase
{
    /**
     * @var ExecutionContext
     */
    protected $executionContext;

    /**
     * @var Pim/Bundle/ProductBundle/Validator/Constraints/ValidMetricAttribute
     */
    protected $constraint;

    /**
     * @var Pim/Bundle/ProductBundle/Validator/Constraints/ValidMetricAttributeValidator
     */
    protected $validator;

    /**
     * @var array Measures
     */
    protected $measures;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->executionContext = $this->initExecutionContext();

        static::$kernel = static::createKernel(array('environment' => 'dev'));
        static::$kernel->boot();

        $this->measures = static::$kernel->getContainer()->getParameter('oro_measure.measures_config');

        $this->constraint = new ValidMetricAttribute();

        $this->validator = new ValidMetricAttributeValidator($this->measures);
        $this->validator->initialize($this->executionContext);
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown()
    {
        $this->executionContext = null;

        parent::tearDown();
    }

    /**
     * Initialize execution context for validator with mock objects
     *
     * @return \Symfony\Component\Validator\ExecutionContext
     */
    protected function initExecutionContext()
    {
        $graphWalker = $this->getMock('Symfony\Component\Validator\GraphWalker', array(), array(), '', false);
        $metadataFactory = $this->getMock('Symfony\Component\Validator\Mapping\ClassMetadataFactoryInterface');
        $globalContext = new GlobalExecutionContext('Root', $graphWalker, $metadataFactory);

        return new ExecutionContext($globalContext, 'currentValue', 'foo.bar', 'Group', 'ClassName', 'propertyName');
    }

    /**
     * Create a product attribute entity
     * @param string $metricType Metric type
     * @param string $metricUnit Default metric unit
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductAttribute
     */
    protected function createProductAttribute($metricType, $metricUnit = '')
    {
        $productAttribute = new ProductAttribute();

        $attribute = new Attribute();
        $productAttribute->setAttribute($attribute);

        $productAttribute->setAttributeType(AbstractAttributeType::TYPE_METRIC_CLASS);
        $productAttribute->setMetricType($metricType);
        $productAttribute->setDefaultMetricUnit($metricUnit);

        return $productAttribute;
    }

    /**
     * Test case with invalid metric type
     * @param string $metricType Metric type
     *
     * @dataProvider providerMetricTypeInvalid
     */
    public function testMetricTypeInvalid($metricType)
    {
        $productAttribute = $this->createProductAttribute($metricType);

        $this->validator->validate($productAttribute, $this->constraint);

        $this->assertCount(1, $this->executionContext->getViolations());
        foreach ($this->executionContext->getViolations() as $violation) {
            $this->assertEquals(
                $this->constraint->invalidMetricTypeMessage,
                $violation->getMessageTemplate()
            );
        }
    }

    /**
     * Provider for metric type violation
     * @return array
     *
     * @static
     */
    public static function providerMetricTypeInvalid()
    {
        return array(
            array('invalid_type_1'),
            array('invalid_type_2'),
            array('invalid_type_3')
        );
    }

    /**
     * Test case with invalid metric unit
     * @param string $metricUnit Metric unit
     *
     * @dataProvider providerMetricUnitInvalid
     */
    public function testMetricUnitInvalid($metricUnit)
    {
        $metricType = key($this->measures['measures_config']);
        $productAttribute = $this->createProductAttribute($metricType, $metricUnit);

        $this->validator->validate($productAttribute, $this->constraint);

        $this->assertCount(1, $this->executionContext->getViolations());
        foreach ($this->executionContext->getViolations() as $violation) {
            $this->assertEquals(
                $this->constraint->invalidMetricUnitMessage,
                $violation->getMessageTemplate()
            );
        }
    }

    /**
     * Provider for metric unit violation
     * @return array
     *
     * @static
     */
    public static function providerMetricUnitInvalid()
    {
        return array(
            array('invalid_unit_1'),
            array('invalid_unit_2'),
            array('invalid_unit_3')
        );
    }

    /**
     * Test case with valid metric type and unit
     */
    public function testMetricTypeAndUnitValid()
    {
        $metricType = key($this->measures['measures_config']);
        $metricUnit = $this->measures['measures_config'][$metricType]['standard'];
        $productAttribute = $this->createProductAttribute($metricType, $metricUnit);

        $this->validator->validate($productAttribute, $this->constraint);

        $this->assertCount(0, $this->executionContext->getViolations());
    }

    /**
     * Test validatedBy method
     */
    public function testValidatedBy()
    {
        $this->assertEquals($this->constraint->validatedBy(), 'pim_metric_attribute_validator');
    }

    /**
     * Test getTargets method
     */
    public function testGetTargets()
    {
        $this->assertEquals($this->constraint->getTargets(), 'class');
    }
}
