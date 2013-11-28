<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\FlexibleEntityBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Validator\Constraints\ProductValueNotBlank;
use Pim\Bundle\CatalogBundle\Validator\Constraints\ProductValueNotBlankValidator;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Entity\Currency;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNotBlankValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductValueNotBlankValidator
     */
    protected $validator;

    /**
     * @var ProductValueNotBlank
     */
    protected $constraint;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->validator = new ProductValueNotBlankValidator();
        $this->constraint = new ProductValueNotBlank(array('channel' => $this->getChannel()));
    }

    /**
     * Assert validation with null
     */
    public function testNullValue()
    {
        $context = $this->getExecutionContext();
        $context
            ->expects($this->once())
            ->method('addViolation')
            ->with($this->constraint->messageNotNull);

        $this->validator->initialize($context);
        $this->validator->validate(null, $this->constraint);
    }

    /**
     * Assert validation with wrong entity
     */
    public function testWithWrongEntity()
    {
        $context = $this->getExecutionContext();
        $context
            ->expects($this->never())
            ->method('addViolation');

        $this->validator->initialize($context);

        $this->validator->validate(new \stdClass(), $this->constraint);
    }

    /**
     * Data provider for right simple data
     *
     * @static
     * @return array
     */
    public static function dataProviderWithRightSimpleData()
    {
        $attribute = new ProductAttribute();
        $attribute->setCode('price');
        $attribute->setAttributeType('pim_catalog_price_collection')->setBackendType('prices');
        $price = new ProductPrice();
        $price->setCurrency('EUR');
        $price->setData(12.5);

        return array(
            array('char' => 'a'),
            array('string' => 'test'),
            array('sentence' => 'juste a sentence'),
            array('integer' => 5),
            array('zero' => 0),
            array('float' => 3.4),
            array('zero float' => 0.0),
            array('negative integer' => -2),
            array('negative float' => -5.3),
            array('negative zero' => -0),
            array('negative zero float' => -0.00),
            array('boolean true' => true),
            array('boolean false' => false),
            array('not empty array' => array('A')),
            array('object' => new \stdClass()),
            array('not empty option collection' => new ArrayCollection(array(new AttributeOption()))),
            array('expected price collection' => new ArrayCollection(array($price)), $attribute),
        );
    }

    /**
     * Assert validation with simple right data (string, int, etc.)
     * @param mixed $return
     * @param mixed $attribute
     *
     * @dataProvider dataProviderWithRightSimpleData
     */
    public function testWithRightSimpleData($return, $attribute = null)
    {
        $context = $this->getExecutionContext();
        $context
            ->expects($this->never())
            ->method('addViolation');

        $productValue = $this->getProductValueMock($return, $attribute);

        $this->validator->initialize($context);
        $this->validator->validate($productValue, $this->constraint);
    }

    /**
     * Data provider for wrong simple data
     *
     * @static
     * @return array
     */
    public static function dataProviderWithWrongSimpleData()
    {
        $attribute = new ProductAttribute();
        $attribute->setCode('price');
        $attribute->setAttributeType('pim_catalog_price_collection')->setBackendType('prices');
        $price = new ProductPrice();
        $price->setCurrency('EUR');
        $price->setData(null);

        return array(
            array('null' => null),
            array('empty string' => ''),
            array('empty option collection' => new ArrayCollection()),
            array('unexpected price collection' => new ArrayCollection(array($price)), $attribute),
        );
    }

    /**
     * Assert validation with simple wrong data (null, empty string, empty array)
     * @param mixed $return
     * @param mixed $attribute
     *
     * @dataProvider dataProviderWithWrongSimpleData
     */
    public function testWithWrongSimpleData($return, $attribute = null)
    {
        $context = $this->getExecutionContext();
        $context
            ->expects($this->once())
            ->method('addViolation');

        $productValue = $this->getProductValueMock($return, $attribute);

        $this->validator->initialize($context);
        $this->validator->validate($productValue, $this->constraint);
    }

    /**
     * Get execution context
     *
     * @return \Symfony\Component\Validator\ExecutionContext
     */
    protected function getExecutionContext()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Get a product value mock
     *
     * @param mixed            $return
     * @param ProductAttribute $attribute
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductValue
     */
    protected function getProductValueMock($return, $attribute = null)
    {
        $productValue = $this->getMock('Pim\Bundle\CatalogBundle\Model\ProductValue');

        $productValue
            ->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($return));

        $productValue
            ->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));

        return $productValue;
    }

    /**
     * @return Channel
     */
    protected function getChannel()
    {
        $channel = new Channel();
        $channel->setCode('catalog');
        $currency = new Currency();
        $currency->setCode('EUR');
        $channel->addCurrency($currency);

        return $channel;
    }
}
