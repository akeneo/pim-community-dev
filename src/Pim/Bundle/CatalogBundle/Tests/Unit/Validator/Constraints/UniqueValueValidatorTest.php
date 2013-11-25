<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Validator\Constraints\UniqueValueValidator;
use Pim\Bundle\CatalogBundle\Validator\Constraints\UniqueValue;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueValueValidatorTest extends \PHPUnit_Framework_TestCase
{
    const VALID_PROPERTY_PATH   = 'children[values].children[sku].children[varchar].data';
    const INVALID_PROPERTY_PATH = 'foo';

    protected $context;
    protected $validator;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $doctrine      = $this->getManagerRegistryMock();
        $manager = $this->getObjectManagerMock();
        $this->form    = $this->getFormMock();
        $this->context = $this->getExecutionContextMock();
        $this->repository = $this->getEntityRepositoryMock();

        $this
            ->context
            ->expects($this->any())
            ->method('getRoot')
            ->will($this->returnValue($this->form));

        $doctrine
            ->expects($this->any())
            ->method('getManagerForClass')
            ->will($this->returnValue($manager));

        $manager
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->repository));

        $this->validator = new UniqueValueValidator($doctrine);
        $this->validator->initialize($this->context);
    }

    /**
     * Test related method
     */
    public function testInvalidPropertyPath()
    {
        $this
            ->context
            ->expects($this->any())
            ->method('getPropertyPath')
            ->will($this->returnValue(self::INVALID_PROPERTY_PATH));

        $this
            ->context
            ->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('bar', new UniqueValue());
    }

    /**
     * Test related method
     */
    public function testNonFormProductData()
    {
        $this
            ->context
            ->expects($this->any())
            ->method('getPropertyPath')
            ->will($this->returnValue(self::VALID_PROPERTY_PATH));

        $this
            ->form
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(new \StdClass()));

        $this
            ->context
            ->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('bar', new UniqueValue());
    }

    /**
     * Test related method
     */
    public function testNonExistingProductAttribute()
    {
        $product = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');
        $this
            ->context
            ->expects($this->any())
            ->method('getPropertyPath')
            ->will($this->returnValue(self::VALID_PROPERTY_PATH));

        $this
            ->form
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($product));

        $product
            ->expects($this->any())
            ->method('getValue')
            ->with('sku')
            ->will($this->returnValue(false));

        $this
            ->context
            ->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('bar', new UniqueValue());
    }

    /**
     * Test related method
     */
    public function testNonProductValue()
    {
        $product = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');
        $this
            ->context
            ->expects($this->any())
            ->method('getPropertyPath')
            ->will($this->returnValue(self::VALID_PROPERTY_PATH));

        $this
            ->form
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($product));

        $product
            ->expects($this->any())
            ->method('getValue')
            ->with('sku')
            ->will($this->returnValue(new \StdClass()));

        $this
            ->context
            ->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('bar', new UniqueValue());
    }

    /**
     * Test related method
     */
    public function testValidValueBecauseNoResult()
    {
        $product   = $this->getProductMock();
        $value     = $this->getProductValueMock();
        $attribute = $this->getProductAttributeMock();

        $this
            ->context
            ->expects($this->any())
            ->method('getPropertyPath')
            ->will($this->returnValue(self::VALID_PROPERTY_PATH));

        $this
            ->form
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($product));

        $product
            ->expects($this->any())
            ->method('getValue')
            ->with('sku')
            ->will($this->returnValue($value));

        $value
            ->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));

        $attribute
            ->expects($this->any())
            ->method('getBackendType')
            ->will($this->returnValue('varchar'));

        $this
            ->repository
            ->expects($this->any())
            ->method('findBy')
            ->with(
                array(
                    'attribute' => $attribute,
                    'varchar' => 'bar'
                )
            )
            ->will($this->returnValue(array()));

        $this
            ->context
            ->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('bar', new UniqueValue());
    }

    /**
     * Test related method
     */
    public function testValidValueBecauseSameProduct()
    {
        $product   = $this->getProductMock();
        $value     = $this->getProductValueMock();
        $attribute = $this->getProductAttributeMock();

        $this
            ->context
            ->expects($this->any())
            ->method('getPropertyPath')
            ->will($this->returnValue(self::VALID_PROPERTY_PATH));

        $this
            ->form
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($product));

        $product
            ->expects($this->any())
            ->method('getValue')
            ->with('sku')
            ->will($this->returnValue($value));

        $value
            ->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));

        $attribute
            ->expects($this->any())
            ->method('getBackendType')
            ->will($this->returnValue('varchar'));

        $this
            ->repository
            ->expects($this->any())
            ->method('findBy')
            ->with(
                array(
                    'attribute' => $attribute,
                    'varchar' => 'bar'
                )
            )
            ->will($this->returnValue(array($value)));

        $this
            ->context
            ->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('bar', new UniqueValue());
    }

    /**
     * Test related method
     */
    public function testInvalidValue()
    {
        $product   = $this->getProductMock();
        $value     = $this->getProductValueMock();
        $attribute = $this->getProductAttributeMock();

        $this
            ->context
            ->expects($this->any())
            ->method('getPropertyPath')
            ->will($this->returnValue(self::VALID_PROPERTY_PATH));

        $this
            ->form
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($product));

        $product
            ->expects($this->any())
            ->method('getValue')
            ->with('sku')
            ->will($this->returnValue($value));

        $value
            ->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));

        $attribute
            ->expects($this->any())
            ->method('getBackendType')
            ->will($this->returnValue('varchar'));

        $this
            ->repository
            ->expects($this->any())
            ->method('findBy')
            ->with(
                array(
                    'attribute' => $attribute,
                    'varchar' => 'bar'
                )
            )
            ->will($this->returnValue(array($this->getProductValueMock())));

        $constraint = new UniqueValue();
        $this
            ->context
            ->expects($this->once())
            ->method('addViolation')
            ->with(
                $constraint->message
            );

        $this->validator->validate('bar', $constraint);
    }

    /**
     * @return \Doctrine\Common\Persistence\ManagerRegistry
     */
    private function getManagerRegistryMock()
    {
        return $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
    }

    /**
     * @return \Symfony\Component\Validator\ExecutionContext
     */
    private function getExecutionContextMock()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \Symfony\Component\Form\Form
     */
    private function getFormMock()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    public function getObjectManagerMock()
    {
        return $this->getMock('Doctrine\Common\Persistence\ObjectManager');
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getEntityRepositoryMock()
    {
        return $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Entity\Product
     */
    public function getProductMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Entity\ProductValue
     */
    public function getProductValueMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Model\ProductValue');
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Entity\ProductAttribute
     */
    public function getProductAttributeMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Entity\ProductAttribute');
    }
}
