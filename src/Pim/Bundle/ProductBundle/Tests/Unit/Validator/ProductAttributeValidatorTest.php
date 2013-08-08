<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Validator;

use Pim\Bundle\ProductBundle\Entity\AttributeOption;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Pim\Bundle\ProductBundle\Validator\ProductAttributeValidator;

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
    protected $context;
    protected $validator;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $this->validator = new ProductAttributeValidator();
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown()
    {
        $this->context = null;
        $this->validator = null;

        parent::tearDown();
    }

    /**
     * Test case with invalid attribute option default values
     * @param string $attributeType
     * @param array  $optionValues
     * @param string $expectedViolation
     *
     * @dataProvider providerAttributeOptionsInvalid
     */
    public function testAttributeOptionsInvalid($attributeType, $optionValues, $expectedViolation)
    {
        $attribute = $this->createAttribute($attributeType);

        foreach ($optionValues as $value) {
            $attributeOption = new AttributeOption();
            $attributeOption->setDefaultValue($value);
            $attribute->addOption($attributeOption);
        }

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with($expectedViolation);

        ProductAttributeValidator::areOptionsValid($attribute, $this->context);
    }

    /**
     * Provider for attribute option default value constraint violation
     * @return array
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
     * Provider for no default value violations
     * @return array
     *
     * @static
     */
    public static function providerDefaultValueValid()
    {
        return array(
            array(
                'pim_product_date',
                array(
                    'defaultValue' => new \DateTime('+1 month'),
                    'dateType'     => 'datetime',
                    'dateMin'      => new \DateTime('now'),
                    'dateMax'      => new \DateTime('+1 year')
                )
            ),
            array(
                'pim_product_price_collection',
                array(
                    'defaultValue'    => 9.99,
                    'numberMin'       => 0.01,
                    'numberMax'       => 1000000,
                    'decimalsAllowed' => true,
                    'negativeAllowed' => false
                )
            ),
            array(
                'pim_product_number',
                array(
                    'defaultValue'    => -10,
                    'numberMin'       => -100.1,
                    'numberMax'       => 100,
                    'decimalsAllowed' => true,
                    'negativeAllowed' => true
                )
            ),
            array(
                'pim_product_textarea',
                array(
                    'defaultValue'  => 'test value',
                    'maxCharacters' => 200
                )
            ),
            array(
                'pim_product_metric',
                array(
                    'defaultValue'      => 20,
                    'numberMin'         => -273,
                    'numberMax'         => 1000,
                    'decimalsAllowed'   => false,
                    'negativeAllowed'   => true,
                    'metricFamily'      => 'temperature',
                    'defaultMetricUnit' => 'C'
                )
            ),
            array(
                'pim_product_text',
                array(
                    'defaultValue'     => 'Test123',
                    'maxCharacters'    => 100,
                    'validationRule'   => 'regexp',
                    'validationRegexp' => '#[[:alnum:]]#'
                )
            ),
            array(
                'pim_product_text',
                array(
                    'defaultValue'   => 'user@sub.domain.museum',
                    'validationRule' => 'email'
                )
            ),
            array(
                'pim_product_text',
                array(
                    'defaultValue'   => 'http://symfony.com/',
                    'validationRule' => 'url'
                )
            ),
            array(
                'pim_product_boolean',
                array('
                    defaultValue' => true
                )
            )
        );
    }

    /**
     * Test case without default value violations
     * @param string $attributeType
     * @param array  $properties
     *
     * @dataProvider providerDefaultValueValid
     */
    public function testDefaultValueValid($attributeType, $properties)
    {
        $this->context->expects($this->never())->method('addViolation');
        $this->context->expects($this->never())->method('addViolationAt');

        $attribute = $this->createAttribute($attributeType, $properties);

        ProductAttributeValidator::isDefaultValueValid($attribute, $this->context);
    }

    /**
     * Provider for inivalid default value
     * @return array
     *
     * @static
     */
    public static function providerDefaultValueInvalid()
    {
        return array(
            array(
                'pim_product_date',
                array(
                    'defaultValue' => new \DateTime('now'),
                    'dateMin'      => new \DateTime('+1 day'),
                    'dateMax'      => new \DateTime('-1 day')
                )
            ),
            array(
                'pim_product_price_collection',
                array(
                    'defaultValue' => 1,
                    'numberMin'    => 5.5,
                )
            ),
            array(
                'pim_product_number',
                array(
                    'defaultValue'    => -100,
                    'negativeAllowed' => false
                )
            ),
            array(
                'pim_product_metric',
                array(
                    'defaultValue'    => 0.1,
                    'decimalsAllowed' => false
                )
            ),
            array(
                'pim_product_textarea',
                array(
                    'defaultValue'  => 'test value',
                    'maxCharacters' => 5
                )
            ),
            array(
                'pim_product_text',
                array(
                    'defaultValue'     => 'Test123',
                    'validationRule'   => 'regexp',
                    'validationRegexp' => '',
                    'maxCharacters'    => 100
                )
            ),
            array(
                'pim_product_text',
                array(
                    'defaultValue'     => 'Some text',
                    'validationRule'   => 'regexp',
                    'validationRegexp' => '/^\d+$/'
                )
            ),
            array(
                'pim_product_boolean',
                array(
                    'defaultValue' => 5
                )
            )
        );
    }

    /**
     * Test case with invalid default value
     * @param string $attributeType
     * @param array  $properties
     *
     * @dataProvider providerDefaultValueInvalid
     */
    public function testDefaultValueInvalid($attributeType, $properties)
    {
        $attribute = $this->createAttribute($attributeType, $properties);

        $this->context->expects($this->once())->method('addViolationAt');

        ProductAttributeValidator::isDefaultValueValid($attribute, $this->context);
    }

    /**
     * Create a ProductAttribute entity
     * @param string $attributeType
     * @param array  $properties
     *
     * @return ProductAttribute
     */
    protected function createAttribute($attributeType, $properties = array())
    {
        $attribute = new ProductAttribute();

        $attribute->setAttributeType($attributeType);

        foreach ($properties as $property => $value) {
            $set = 'set' . ucfirst($property);
            if (method_exists($attribute, $set)) {
                $attribute->$set($value);
            }
        }

        return $attribute;
    }
}
