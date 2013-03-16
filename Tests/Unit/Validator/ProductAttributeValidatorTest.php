<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Validator;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;

use Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption;

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
    protected function createProductAttribute($attributeType, $code, $unique, $translatable, $searchable, $smart, $scopable, $properties = array())
    {
        // instanciate product attribute
        $productAttribute = new ProductAttribute();

        // add attribute
        $attribute = new Attribute();
        $productAttribute->setAttribute($attribute);

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
    public function testUniqueConstraintInvalid($attributeType, $code, $unique, $translatable, $searchable, $smart, $scopable)
    {
        $productAttribute =
            $this->createProductAttribute($attributeType, $code, $unique, $translatable, $searchable, $smart, $scopable);

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
            array(AbstractAttributeType::TYPE_TEXT_CLASS, 'code1', true, true, false, false, true),
            array(AbstractAttributeType::TYPE_TEXT_CLASS, 'code2', true, false, false, false, true),
            array(AbstractAttributeType::TYPE_TEXT_CLASS, 'code3', true, true, false, false, false),
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
    public function testMatrixConstraintInvalid($attributeType, $code, $unique, $translatable, $searchable, $smart, $scopable)
    {
        $productAttribute =
            $this->createProductAttribute($attributeType, $code, $unique, $translatable, $searchable, $smart, $scopable);

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
            array(AbstractAttributeType::TYPE_TEXTAREA_CLASS, 'code1', true, false, false, false, false),
            array(AbstractAttributeType::TYPE_MONEY_CLASS, 'code2', true, false, false, false, false),
            array(AbstractAttributeType::TYPE_OPT_MULTI_SELECT_CLASS, 'code4', true, false, false, false, false),
            array(AbstractAttributeType::TYPE_OPT_SINGLE_SELECT_CLASS, 'code6', true, false, false, false, false),
            array(AbstractAttributeType::TYPE_IMAGE_CLASS, 'code7', true, false, false, false, false),
            array(AbstractAttributeType::TYPE_FILE_CLASS, 'code8', true, false, false, false, false),
            array(AbstractAttributeType::TYPE_METRIC_CLASS, 'code9', true, false, false, false, false),
            array(AbstractAttributeType::TYPE_BOOLEAN_CLASS, 'code10', true, false, false, false, false),
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
            array(AbstractAttributeType::TYPE_MONEY_CLASS, 'code1', true, true, false, false, false),
            array(AbstractAttributeType::TYPE_METRIC_CLASS, 'code2', true, true, false, false, false),
            array(AbstractAttributeType::TYPE_METRIC_CLASS, 'code3', true, false, false, false, true),
            array(AbstractAttributeType::TYPE_METRIC_CLASS, 'code4', true, true, false, false, true),
            array(AbstractAttributeType::TYPE_METRIC_CLASS, null, true, false, false, false, false),
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
        $productAttribute =
            $this->createProductAttribute($attributeType, $code, $unique, $translatable, $searchable, $smart, $scopable);

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
            array(AbstractAttributeType::TYPE_MONEY_CLASS, 'code1', false, false, false, false, false),
            array(AbstractAttributeType::TYPE_TEXT_CLASS, 'code2', true, false, false, false, false),
            array(AbstractAttributeType::TYPE_TEXT_CLASS, 'code3', true, false, false, true, false),
            array(AbstractAttributeType::TYPE_TEXT_CLASS, 'code4', true, false, true, false, false),
            array(AbstractAttributeType::TYPE_TEXT_CLASS, 'code5', true, false, true, true, false),
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
        $productAttribute =
            $this->createProductAttribute($attributeType, $code, $unique, $translatable, $searchable, $smart, $scopable);

        // Call validator
        ProductAttributeValidator::isValid($productAttribute, $this->executionContext);

        // assertion
        $this->assertCount(0, $this->executionContext->getViolations());
    }

    /**
     * Test case with invalid attribute option default values
     * @param string $attributeType Attribute type value
     * @param array  $optionValues  Default option values
     *
     * @dataProvider providerAttributeOptionsInvalid
     */
    public function testAttributeOptionsInvalid($attributeType, $optionValues)
    {
        $productAttribute = $this->createProductAttribute($attributeType, 'code', false, true, false, false, false);
        foreach ($optionValues as $value) {
            $attributeOption = new AttributeOption();
            $attributeOption->setDefaultValue($value);
            $productAttribute->getAttribute()->addOption($attributeOption);
        }

        // Call validator
        ProductAttributeValidator::isValid($productAttribute, $this->executionContext);

        // assertion
        $this->assertCount(1, $this->executionContext->getViolations());
        foreach ($this->executionContext->getViolations() as $violation) {
            $this->assertEquals(
                ProductAttributeValidator::VIOLATION_OPTION_DEFAULT_VALUE_REQUIRED,
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
            array(AbstractAttributeType::TYPE_OPT_MULTI_SELECT_CLASS, array('a', 'b', null)),
            array(AbstractAttributeType::TYPE_OPT_SINGLE_SELECT_CLASS, array(1, null, 3)),
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
            array(AbstractAttributeType::TYPE_DATE_CLASS, 'code1',
                array('defaultValue' => new \DateTime('+1 month'), 'dateType' => 'datetime', 'dateMin' => new \DateTime('now'), 'dateMax' => new \DateTime('+1 year'))),
            array(AbstractAttributeType::TYPE_INTEGER_CLASS, 'code2',
                array('defaultValue' => 10, 'numberMin' => 1, 'numberMax' => 100.1, 'decimalPlaces' => 2, 'negativeAllowed' => false)),
            array(AbstractAttributeType::TYPE_MONEY_CLASS, 'code3',
                array('defaultValue' => 9.99, 'numberMin' => 0.01, 'numberMax' => 1000000, 'decimalPlaces' => 2,
                    'negativeAllowed' => false)),
            array(AbstractAttributeType::TYPE_NUMBER_CLASS, 'code4',
                array('defaultValue' => -10, 'numberMin' => -100, 'numberMax' => 100, 'decimalPlaces' => 4, 'negativeAllowed' => true)),
            array(AbstractAttributeType::TYPE_NUMBER_CLASS, 'code4',
                array('numberMin' => 1.1, 'numberMax' => 2.2, 'decimalPlaces' => 4, 'negativeAllowed' => true)),
            array(AbstractAttributeType::TYPE_OPT_MULTI_SELECT_CLASS, 'code5',
                array('valueCreationAllowed' => true)),
            array(AbstractAttributeType::TYPE_OPT_SINGLE_SELECT_CLASS, 'code6',
                array('defaultValue' => 'test value')),
            array(AbstractAttributeType::TYPE_TEXTAREA_CLASS, 'code7',
                array('defaultValue' => 'test value', 'maxCharacters' => 200, 'wysiwygEnabled' => true)),
            array(AbstractAttributeType::TYPE_METRIC_CLASS, 'code8',
                array('defaultValue' => 20, 'numberMin' => -273, 'numberMax' => 1000, 'decimalPlaces' => 4,
                    'negativeAllowed' => true, 'metricType' => 'temperature', 'defaultMetricUnit' => 'C')),
            array(AbstractAttributeType::TYPE_FILE_CLASS, 'code9',
                array('allowedFileSources' => 'all', 'maxFileSize' => 10000)),
            array(AbstractAttributeType::TYPE_IMAGE_CLASS, 'code10',
                array('allowedFileSources' => 'upload', 'maxFileSize' => null)),
            array(AbstractAttributeType::TYPE_TEXT_CLASS, 'code11',
                array('defaultValue' => 'Test123', 'maxCharacters' => 100, 'validationRule' => 'regexp', 'validationRegexp' => '#[[:alnum:]]#')),
            array(AbstractAttributeType::TYPE_TEXT_CLASS, 'code12',
                array('defaultValue' => 'user@sub.domain.museum', 'validationRule' => 'email')),
            array(AbstractAttributeType::TYPE_TEXT_CLASS, 'code13',
                array('defaultValue' => 'http://symfony.com/', 'validationRule' => 'url')),
            array(AbstractAttributeType::TYPE_TEXT_CLASS, 'code14',
                array('defaultValue' => 'value', 'maxCharacters' => 10)),
            array(AbstractAttributeType::TYPE_BOOLEAN_CLASS, 'code15',
                array('defaultValue' => true))
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
            array(AbstractAttributeType::TYPE_DATE_CLASS, 'code1', array(
                'defaultValue' => new \DateTime('now'), 'dateType' => 'H:i',
                'dateMin' => new \DateTime('+1 day'), 'dateMax' => new \DateTime('-1 day')), 3),
            array(AbstractAttributeType::TYPE_DATE_CLASS, 'code2',
                array('defaultValue' => new \DateTime('now'), 'dateType' => 'date',
                    'dateMin' => null, 'dateMax' => new \DateTime('-1 day')), 1),
            array(AbstractAttributeType::TYPE_DATE_CLASS, 'code3',
                array('defaultValue' => date('d/m/Y'), 'dateType' => 'time',
                    'dateMin' => new \DateTime('-1 day'), 'dateMax' => new \DateTime('-1 day')), 2),
            array(AbstractAttributeType::TYPE_DATE_CLASS, 'code4', array(
                'dateType' => 'date', 'dateMin' => 1, 'dateMax' => 2), 2),
            array(AbstractAttributeType::TYPE_INTEGER_CLASS, 'code5',
                array('defaultValue' => -10, 'numberMin' => 1.5, 'numberMax' => -2,
                    'negativeAllowed' => false), 3),
            array(AbstractAttributeType::TYPE_INTEGER_CLASS, 'code6',
                array('defaultValue' => -2, 'numberMin' => -1, 'numberMax' => 2,
                    'negativeAllowed' => true), 1),
            array(AbstractAttributeType::TYPE_INTEGER_CLASS, 'code7',
                array('defaultValue' => 10, 'numberMin' => -1, 'numberMax' => 5,
                    'negativeAllowed' => false), 2),
            array(AbstractAttributeType::TYPE_MONEY_CLASS, 'code8',
                array('defaultValue' => 9.999, 'numberMin' => -0.01, 'numberMax' => 1000000,
                    'decimalPlaces' => 2, 'negativeAllowed' => false), 2),
            array(AbstractAttributeType::TYPE_MONEY_CLASS, 'code9',
                array('defaultValue' => 1, 'numberMin' => 5, 'decimalPlaces' => -1, 'negativeAllowed' => false), 2),
            array(AbstractAttributeType::TYPE_MONEY_CLASS, 'code10',
                array('defaultValue' => 0, 'numberMax' => -1, 'negativeAllowed' => false), 2),
            array(AbstractAttributeType::TYPE_MONEY_CLASS, 'code11',
                array('defaultValue' => -1, 'negativeAllowed' => false), 1),
            array(AbstractAttributeType::TYPE_NUMBER_CLASS, 'code12',
                array('defaultValue' => -100, 'numberMin' => -10.111111, 'numberMax' => null,
                    'decimalPlaces' => 5, 'negativeAllowed' => true), 3),
            array(AbstractAttributeType::TYPE_TEXTAREA_CLASS, 'code13',
                array('defaultValue' => 'test value', 'maxCharacters' => 5,
                    'wysiwygEnabled' => true), 1),
            array(AbstractAttributeType::TYPE_METRIC_CLASS, 'code14',
                array('defaultValue' => 0, 'numberMin' => -2, 'numberMax' => -1,
                    'decimalPlaces' => -1, 'negativeAllowed' => false,
                    'metricType' => 'temperature', 'defaultMetricUnit' => 'C'), 4),
            array(AbstractAttributeType::TYPE_FILE_CLASS, 'code15',
                array('allowedFileSources' => 'other', 'maxFileSize' => -1, 'defaultValue' => 1), 3),
            array(AbstractAttributeType::TYPE_TEXT_CLASS, 'code16',
                array('defaultValue' => 'Test123', 'maxCharacters' => '',
                    'validationRule' => 'regexp', 'validationRegexp' => ''), 3),
            array(AbstractAttributeType::TYPE_TEXT_CLASS, 'code17',
                array('defaultValue' => 'Test123', 'maxCharacters' => 1,
                    'validationRule' => 'text', 'validationRegexp' => '/[^0-9]/'), 3),
            array(AbstractAttributeType::TYPE_TEXT_CLASS, 'code18',
                array('defaultValue' => '_', 'validationRule' => 'regexp',
                    'validationRegexp' => '#[[:alnum:]]#'), 1),
            array(AbstractAttributeType::TYPE_TEXT_CLASS, 'code19',
                array('defaultValue' => 'email', 'validationRule' => 'email'), 1),
            array(AbstractAttributeType::TYPE_TEXT_CLASS, 'code20',
                array('defaultValue' => 'www.test.com', 'validationRule' => 'url'), 1),
            array(AbstractAttributeType::TYPE_TEXT_CLASS, 'code21',
                array('validationRule' => 'text'), 1),
            array(AbstractAttributeType::TYPE_BOOLEAN_CLASS, 'code22', array('defaultValue' => 5), 1)
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
