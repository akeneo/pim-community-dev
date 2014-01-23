<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator;

use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidator;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeValidatorTest extends \PHPUnit_Framework_TestCase
{
    protected $context;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->context = $this->getMock('Symfony\Component\Validator\ExecutionContext', [], [], '', false);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $this->context = null;

        parent::tearDown();
    }

    /**
     * Test case with invalid attribute option codes
     * @param string $attributeType
     * @param array  $optionCodes
     * @param string $expectedViolation
     *
     * @dataProvider providerAttributeOptionsInvalid
     */
    public function testAttributeOptionsInvalid($attributeType, $optionCodes, $expectedViolation)
    {
        $attribute = $this->createAttribute($attributeType);

        foreach ($optionCodes as $code) {
            $attributeOption = new AttributeOption();
            $attributeOption->setCode($code);
            $attribute->addOption($attributeOption);
        }

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with($expectedViolation);

        AttributeValidator::areOptionsValid($attribute, $this->context);
    }

    /**
     * Provider for attribute option code constraint violation
     * @return array
     */
    public static function providerAttributeOptionsInvalid()
    {
        return [
            [
                'pim_catalog_multiselect',
                ['a', 'b', null],
                AttributeValidator::VIOLATION_OPTION_CODE_REQUIRED
            ],
            [
                'pim_catalog_simpleselect',
                [1, null, 3],
                AttributeValidator::VIOLATION_OPTION_CODE_REQUIRED
            ],
            [
                'pim_catalog_simpleselect',
                ['a', 'a', 'b'],
                AttributeValidator::VIOLATION_DUPLICATE_OPTION_CODE
            ],
        ];
    }

    /**
     * Create a Attribute entity
     * @param string $attributeType
     * @param array  $properties
     *
     * @return Attribute
     */
    protected function createAttribute($attributeType, $properties = [])
    {
        $attribute = new Attribute();

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
