<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Validator;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;

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
     *
     * @return \Pim\Bundle\ProductBundle\Entity\ProductAttribute
     */
    protected function createProductAttribute($attributeType, $code, $unique, $translatable, $searchable, $smart, $scopable)
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
     * Test case with unique constraint invalid
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
            array(AbstractAttributeType::TYPE_OPT_MULTI_CB_CLASS, 'code3', true, false, false, false, false),
            array(AbstractAttributeType::TYPE_OPT_MULTI_SELECT_CLASS, 'code4', true, false, false, false, false),
            array(AbstractAttributeType::TYPE_OPT_SINGLE_RADIO_CLASS, 'code5', true, false, false, false, false),
            array(AbstractAttributeType::TYPE_OPT_SINGLE_SELECT_CLASS, 'code6', true, false, false, false, false),
            array(AbstractAttributeType::TYPE_IMAGE_CLASS, 'code7', true, false, false, false, false),
            array(AbstractAttributeType::TYPE_FILE_CLASS, 'code8', true, false, false, false, false),
            array(AbstractAttributeType::TYPE_METRIC_CLASS, 'code9', true, false, false, false, false),
            array('Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\BooleanType', 'code10', true, false, false, false, false),
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
}
