<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Validator;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;

use Pim\Bundle\ProductBundle\Entity\AttributeOption;

use Pim\Bundle\ProductBundle\Entity\ProductAttribute;

use Pim\Bundle\ProductBundle\Validator\ProductAttributeValidator;

use Symfony\Component\Validator\GlobalExecutionContext;

use Symfony\Component\Validator\ExecutionContext;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductAttributeValidatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ExecutionContext
     */
    protected $executionContext;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->markTestSkipped('Due to Symfony 2.3 Upgrade, GlobalExecutionContext issue');
        parent::setUp();

        $this->executionContext = $this->initExecutionContext();
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
     * @param string  $attributeType Attribute type value
     * @param string  $code          Code value
     * @param boolean $unique        Unique value
     * @param boolean $translatable  Translatable value
     * @param boolean $searchable    Searchable value
     * @param boolean $smart         Smart value
     * @param boolean $scopable      Scopable value
     * @param array   $properties    Custom properties
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductAttribute
     */
    protected function createProductAttribute(
        $attributeType,
        $code,
        $unique,
        $translatable,
        $searchable,
        $smart,
        $scopable,
        $properties = array()
    ) {
        // instanciate product attribute
        $productAttribute = new ProductAttribute();

        // set values
        $productAttribute->setAttributeType($attributeType);
        $productAttribute->setCode($code);
        $productAttribute->setUnique($unique);
        $productAttribute->setTranslatable($translatable);
        $productAttribute->setSearchable($searchable);
        $productAttribute->setSmart($smart);
        $productAttribute->setScopable($scopable);

        foreach ($properties as $property => $value) {
            $set = 'set' . ucfirst($property);
            if (method_exists($productAttribute, $set)) {
                $productAttribute->$set($value);
            }
        }

        return $productAttribute;
    }

    /**
     * Test case with unique constraint invalid
     * @param string  $attributeType Attribute type value
     * @param string  $code          Code value
     * @param boolean $unique        Unique value
     * @param boolean $translatable  Translatable value
     * @param boolean $searchable    Searchable value
     * @param boolean $smart         Smart value
     * @param boolean $scopable      Scopable value
     *
     * @dataProvider providerUniqueConstraintInvalid
     */
    public function testUniqueConstraintInvalid(
        $attributeType,
        $code,
        $unique,
        $translatable,
        $searchable,
        $smart,
        $scopable
    ) {
        $productAttribute = $this->createProductAttribute(
            $attributeType,
            $code,
            $unique,
            $translatable,
            $searchable,
            $smart,
            $scopable
        );

        // Call validator
        ProductAttributeValidator::isValid($productAttribute, $this->executionContext);

        // assertion
        $this->assertCount(1, $this->executionContext->getViolations());
        foreach ($this->executionContext->getViolations() as $violation) {
            $this->assertEquals(
                ProductAttributeValidator::VIOLATION_UNIQUE_SCOPE_I18N,
                $violation->getMessageTemplate()
            );
        }
    }

    /**
     * Provider for unique constraint violation
     * @return multitype:multitype:boolean string
     *
     * @static
     */
    public static function providerUniqueConstraintInvalid()
    {
        return array(
            array('pim_product_text', 'code1', true, true, false, false, true),
            array('pim_product_text', 'code2', true, false, false, false, true),
            array('pim_product_text', 'code3', true, true, false, false, false),
        );
    }

    /**
     * Test case with matrix constraint invalid
     * @param string  $attributeType Attribute type value
     * @param string  $code          Code value
     * @param boolean $unique        Unique value
     * @param boolean $translatable  Translatable value
     * @param boolean $searchable    Searchable value
     * @param boolean $smart         Smart value
     * @param boolean $scopable      Scopable value
     *
     * @dataProvider providerMatrixConstraintInvalid
     */
    public function testMatrixConstraintInvalid(
        $attributeType,
        $code,
        $unique,
        $translatable,
        $searchable,
        $smart,
        $scopable
    ) {
        $productAttribute = $this->createProductAttribute(
            $attributeType,
            $code,
            $unique,
            $translatable,
            $searchable,
            $smart,
            $scopable
        );

        // Call validator
        ProductAttributeValidator::isValid($productAttribute, $this->executionContext);

        // assertion
        $this->assertCount(1, $this->executionContext->getViolations());
        foreach ($this->executionContext->getViolations() as $violation) {
            $this->assertEquals(
                ProductAttributeValidator::VIOLATION_UNIQUE_ATT_TYPE,
                $violation->getMessageTemplate()
            );
        }
    }

    /**
     * Provider for attribute type (matrix) constraint violation
     * @return multitype:multitype:boolean string
     *
     * @static
     */
    public static function providerMatrixConstraintInvalid()
    {
        return array(
            array('pim_product_textarea', 'code1', true, false, false, false, false),
            array('pim_product_price_collection', 'code2', true, false, false, false, false),
            array('pim_product_multiselect', 'code4', true, false, false, false, false),
            array('pim_product_simpleselect', 'code6', true, false, false, false, false),
            array('pim_product_image', 'code7', true, false, false, false, false),
            array('pim_product_file', 'code8', true, false, false, false, false),
            array('pim_product_metric', 'code9', true, false, false, false, false),
            array('pim_product_boolean', 'code10', true, false, false, false, false),
        );
    }

    /**
     * Provider for many constraint violation
     * @return multitype:multitype:boolean string
     *
     * @static
     */
    public static function providerManyViolations()
    {
        return array(
            array('pim_product_price_collection', 'code1', true, true, false, false, false),
            array('pim_product_metric', 'code2', true, true, false, false, false),
            array('pim_product_metric', 'code3', true, false, false, false, true),
            array('pim_product_metric', 'code4', true, true, false, false, true),
            array('pim_product_metric', ' ', true, false, false, false, false),
            array('pim_product_metric', '#code', true, false, false, false, false),
            array('pim_product_metric', null, true, false, false, false, false),
        );
    }

    /**
     * Test case with many violations
     * @param string  $attributeType Attribute type value
     * @param string  $code          Code value
     * @param boolean $unique        Unique value
     * @param boolean $translatable  Translatable value
     * @param boolean $searchable    Searchable value
     * @param boolean $smart         Smart value
     * @param boolean $scopable      Scopable value
     *
     * @dataProvider providerManyViolations
     */
    public function testManyViolations($attributeType, $code, $unique, $translatable, $searchable, $smart, $scopable)
    {
        $productAttribute = $this->createProductAttribute(
            $attributeType,
            $code,
            $unique,
            $translatable,
            $searchable,
            $smart,
            $scopable
        );

        // Call validator
        ProductAttributeValidator::isValid($productAttribute, $this->executionContext);

        // assertion
        $this->assertCount(2, $this->executionContext->getViolations());
    }

    /**
     * Provider for no constraint violation
     * @return multitype:multitype:boolean string
     *
     * @static
     */
    public static function providerNoViolation()
    {
        return array(
            array('pim_product_price_collection', 'code1', false, false, false, false, false),
            array('pim_product_text', 'code2', true, false, false, false, false),
            array('pim_product_text', 'code3', true, false, false, true, false),
            array('pim_product_text', 'code4', true, false, true, false, false),
            array('pim_product_text', 'code5', true, false, true, true, false),
        );
    }

    /**
     * Test case without violation
     * @param string  $attributeType Attribute type value
     * @param string  $code          Code value
     * @param boolean $unique        Unique value
     * @param boolean $translatable  Translatable value
     * @param boolean $searchable    Searchable value
     * @param boolean $smart         Smart value
     * @param boolean $scopable      Scopable value
     *
     * @dataProvider providerNoViolation
     */
    public function testNoViolation($attributeType, $code, $unique, $translatable, $searchable, $smart, $scopable)
    {
        $productAttribute = $this->createProductAttribute(
            $attributeType,
            $code,
            $unique,
            $translatable,
            $searchable,
            $smart,
            $scopable
        );

        // Call validator
        ProductAttributeValidator::isValid($productAttribute, $this->executionContext);

        // assertion
        $this->assertCount(0, $this->executionContext->getViolations());
    }

    /**
     * Test case with invalid attribute option default values
     * @param string $attributeType     Attribute type value
     * @param array  $optionValues      Default option values
     * @param string $expectedViolation Expected violation message
     *
     * @dataProvider providerAttributeOptionsInvalid
     */
    public function testAttributeOptionsInvalid($attributeType, $optionValues, $expectedViolation)
    {
        $productAttribute = $this->createProductAttribute($attributeType, 'code', false, true, false, false, false);
        foreach ($optionValues as $value) {
            $attributeOption = new AttributeOption();
            $attributeOption->setDefaultValue($value);
            $productAttribute->addOption($attributeOption);
        }

        // Call validator
        ProductAttributeValidator::isValid($productAttribute, $this->executionContext);

        // assertion
        $this->assertCount(1, $this->executionContext->getViolations());
        foreach ($this->executionContext->getViolations() as $violation) {
            $this->assertEquals(
                $expectedViolation,
                $violation->getMessageTemplate()
            );
        }
    }

    /**
     * Provider for attribute option default value constraint violation
     * @return multitype:multitype:boolean string
     *
     * @static
     */
    public static function providerAttributeOptionsInvalid()
    {
        return array(
            array(
                'pim_product_multiselect',
                array('a', 'b', null),
                ProductAttributeValidator::VIOLATION_OPTION_DEFAULT_VALUE_REQUIRED
            ),
            array(
                'pim_product_simpleselect',
                array(1, null, 3),
                ProductAttributeValidator::VIOLATION_OPTION_DEFAULT_VALUE_REQUIRED
            ),
            array(
                'pim_product_simpleselect',
                array('a', 'a', 'b'),
                ProductAttributeValidator::VIOLATION_DUPLICATE_OPTION_DEFAULT_VALUE
            ),
        );
    }

    /**
     * Provider for no property violations
     * @return multitype:multitype:boolean string
     *
     * @static
     */
    public static function providerNoPropertyViolations()
    {
        return array(
            array(
                'pim_product_date',
                'code1',
                array(
                    'defaultValue' => new \DateTime('+1 month'),
                    'dateType' => 'datetime',
                    'dateMin' => new \DateTime('now'),
                    'dateMax' => new \DateTime('+1 year')
                )
            ),
            array(
                'pim_product_price_collection',
                'code3',
                array(
                    'defaultValue' => 9.99,
                    'numberMin' => 0.01,
                    'numberMax' => 1000000,
                    'decimalsAllowed' => true,
                    'negativeAllowed' => false
                )
            ),
            array(
                'pim_product_number',
                'code4',
                array(
                    'defaultValue' => -10,
                    'numberMin' => -100,
                    'numberMax' => 100,
                    'decimalsAllowed' => false,
                    'negativeAllowed' => true
                )
            ),
            array(
                'pim_product_number',
                'code4',
                array('numberMin' => 1.1, 'numberMax' => 2.2, 'decimalsAllowed' => true, 'negativeAllowed' => true)
            ),
            array(
                'pim_product_multiselect',
                'code5',
                array('valueCreationAllowed' => true)
            ),
            array('pim_product_simpleselect', 'code6', array('defaultValue' => 'test value')),
            array(
                'pim_product_textarea',
                'code7',
                array('defaultValue' => 'test value', 'maxCharacters' => 200, 'wysiwygEnabled' => true)
            ),
            array(
                'pim_product_metric',
                'code8',
                array(
                    'defaultValue' => 20,
                    'numberMin' => -273,
                    'numberMax' => 1000,
                    'decimalsAllowed' => false,
                    'negativeAllowed' => true,
                    'metricFamily' => 'temperature',
                    'defaultMetricUnit' => 'C'
                )
            ),
            array('pim_product_file', 'code9', array('allowedFileSources' => 'all', 'maxFileSize' => 10000)),
            array('pim_product_image', 'code10', array('allowedFileSources' => 'upload', 'maxFileSize' => null)),
            array(
                'pim_product_text',
                'code11',
                array(
                    'defaultValue' => 'Test123',
                    'maxCharacters' => 100,
                    'validationRule' => 'regexp',
                    'validationRegexp' => '#[[:alnum:]]#'
                )
            ),
            array(
                'pim_product_text',
                'code12',
                array('defaultValue' => 'user@sub.domain.museum', 'validationRule' => 'email')
            ),
            array(
                'pim_product_text',
                'code13',
                array('defaultValue' => 'http://symfony.com/', 'validationRule' => 'url')
            ),
            array('pim_product_text', 'code14', array('defaultValue' => 'value', 'maxCharacters' => 10)),
            array('pim_product_boolean', 'code15', array('defaultValue' => true))
        );
    }

    /**
     * Test case without property violations
     * @param string $attributeType Attribute type value
     * @param string $code          Code value
     * @param array  $properties    Custom properties
     *
     * @dataProvider providerNoPropertyViolations
     */
    public function testNoPropertyViolations($attributeType, $code, $properties)
    {
        $productAttribute =
            $this->createProductAttribute($attributeType, $code, false, false, false, false, false, $properties);

        // Call validator
        ProductAttributeValidator::isValid($productAttribute, $this->executionContext);

        // assertion
        $this->assertCount(0, $this->executionContext->getViolations());
    }

    /**
     * Provider for many property violations
     * @return multitype:multitype:boolean string
     *
     * @static
     */
    public static function providerManyPropertyViolations()
    {
        return array(
            array('pim_product_date', 'code1', array(
                'defaultValue' => new \DateTime('now'), 'dateType' => 'H:i',
                'dateMin' => new \DateTime('+1 day'), 'dateMax' => new \DateTime('-1 day')), 3),
            array('pim_product_date', 'code2',
                array('defaultValue' => new \DateTime('now'), 'dateType' => 'date',
                    'dateMin' => null, 'dateMax' => new \DateTime('-1 day')), 1),
            array('pim_product_date', 'code3',
                array('defaultValue' => date('d/m/Y'), 'dateType' => 'time',
                    'dateMin' => new \DateTime('-1 day'), 'dateMax' => new \DateTime('-1 day')), 2),
            array('pim_product_date', 'code4', array(
                'dateType' => 'date', 'dateMin' => 1, 'dateMax' => 2), 2),
            array('pim_product_price_collection', 'code8',
                array('defaultValue' => 9.999, 'numberMin' => -0.01, 'numberMax' => 1000000,
                    'decimalsAllowed' => true, 'negativeAllowed' => false), 1),
            array(
                'pim_product_price_collection',
                'code9',
                array(
                    'defaultValue' => 1,
                    'numberMin' => 5.5,
                    'decimalsAllowed' => false,
                    'negativeAllowed' => false
                ),
                2
            ),
            array('pim_product_price_collection', 'code10',
                array('defaultValue' => 0, 'numberMax' => -1, 'negativeAllowed' => false), 2),
            array('pim_product_price_collection', 'code11',
                array('defaultValue' => -1, 'negativeAllowed' => false), 1),
            array('pim_product_number', 'code12',
                array('defaultValue' => -100, 'numberMin' => -10.111111, 'numberMax' => null,
                    'decimalsAllowed' => false, 'negativeAllowed' => true), 2),
            array('pim_product_textarea', 'code13',
                array('defaultValue' => 'test value', 'maxCharacters' => 5,
                    'wysiwygEnabled' => true), 1),
            array('pim_product_metric', 'code14',
                array('defaultValue' => 0, 'numberMin' => -2, 'numberMax' => -1,
                    'decimalsAllowed' => true, 'negativeAllowed' => false,
                    'metricFamily' => 'temperature', 'defaultMetricUnit' => 'C'), 3),
            array('pim_product_file', 'code15',
                array('allowedFileSources' => 'other', 'maxFileSize' => -1, 'defaultValue' => 1), 3),
            array('pim_product_text', 'code16',
                array('defaultValue' => 'Test123', 'maxCharacters' => '',
                    'validationRule' => 'regexp', 'validationRegexp' => ''), 3),
            array('pim_product_text', 'code17',
                array('defaultValue' => 'Test123', 'maxCharacters' => 1,
                    'validationRule' => 'text', 'validationRegexp' => '/[^0-9]/'), 3),
            array('pim_product_text', 'code18',
                array('defaultValue' => '_', 'validationRule' => 'regexp',
                    'validationRegexp' => '#[[:alnum:]]#'), 1),
            array('pim_product_text', 'code19',
                array('defaultValue' => 'email', 'validationRule' => 'email'), 1),
            array('pim_product_text', 'code20',
                array('defaultValue' => 'www.test.com', 'validationRule' => 'url'), 1),
            array('pim_product_text', 'code21',
                array('validationRule' => 'text'), 1),
            array('pim_product_boolean', 'code22', array('defaultValue' => 5), 1),
            array('pim_product_metric', 'code23',
                array('defaultValue' => 0.1, 'numberMin' => 0, 'numberMax' => 2,
                    'decimalsAllowed' => false, 'negativeAllowed' => false,
                    'metricFamily' => 'temperature', 'defaultMetricUnit' => 'C'), 1)
        );
    }

    /**
     * Test case with many property violations
     * @param string  $attributeType Attribute type value
     * @param string  $code          Code value
     * @param array   $properties    Custom properties
     * @param integer $violations    Number of violations
     *
     * @dataProvider providerManyPropertyViolations
     */
    public function testManyPropertyViolations($attributeType, $code, $properties, $violations)
    {
        $productAttribute =
            $this->createProductAttribute($attributeType, $code, false, false, false, false, false, $properties);

        // Call validator
        ProductAttributeValidator::isValid($productAttribute, $this->executionContext);

        // assertion
        $this->assertCount($violations, $this->executionContext->getViolations());
    }
}
