<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Validator\Import;

use Pim\Bundle\ImportExportBundle\Validator\Import\ProductImportValidator;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductImportValidatorTest extends ImportValidatorTestCase
{
    protected $productValidator;
    protected $constraintGuesser;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->constraintGuesser = $this->getMock(
            'Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface'
        );
        $this->translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $this->translator->expects($this->any())
            ->method('trans')
            ->will($this->returnCallback(array($this, 'translate')));
        $this->productValidator = new ProductImportValidator(
            $this->validator,
            $this->constraintGuesser,
            $this->translator
        );
    }

    /**
     * @param string $message
     * @param array  $params
     *
     * @return string
     */
    public function translate($message, array $params = array())
    {
        return 'trans-' . strtr($message, $params);
    }

    /**
     * Test related method
     * @return null
     */
    public function testValidateProductProperties()
    {
        $test = $this;
        $product = $this->getMockProduct();
        $this->validator->expects($this->exactly(2))
            ->method('validatePropertyValue')
            ->will(
                $this->returnCallback(
                    function ($valProduct, $propertyPath, $value) use ($product, $test) {
                        $test->assertSame($product, $valProduct);
                        if ('key1' === $propertyPath) {
                            return $test->getViolationListMock(array('error "%value%"' => array('%value%' => $value)));
                        } else {
                            return $test->getViolationListMock(array());
                        }
                    }
                )
            );

        $this->assertEquals(
            array('key1: trans-error "val1"'),
            $this->productValidator->validateProductProperties(
                $product,
                array('key1' => 'val1', 'key2' => 'val2')
            )
        );
    }

    /**
     * Test related method
     * @return null
     */
    public function testGetAttributeConstraints()
    {
        $constraints = array(
            'key1' => array(),
            'key2' => array('constraint1')
        );

        $this->constraintGuesser->expects($this->exactly(count($constraints)))
            ->method('supportAttribute')
            ->will(
                $this->returnCallback(
                    function ($attribute) use ($constraints) {
                        return null !== $constraints[$attribute->getCode()];
                    }
                )
            );

        $this->constraintGuesser->expects($this->any())
            ->method('guessConstraints')
            ->will(
                $this->returnCallback(
                    function ($attribute) use ($constraints) {
                        return $constraints[$attribute->getCode()];
                    }
                )
            );

        foreach ($constraints as $code => $attributeConstraints) {
            $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');
            $attribute->expects($this->any())
                ->method('getCode')
                ->will($this->returnValue($code));
            $this->assertEquals(
                $attributeConstraints?:array(),
                $this->productValidator->getAttributeConstraints($attribute)
            );

            // Test constraints are cached
            $this->assertEquals(
                $attributeConstraints?:array(),
                $this->productValidator->getAttributeConstraints($attribute)
            );
        }
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Model\ProductInterface
     */
    protected function getMockProduct()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Model\ProductInterface');
    }
}
