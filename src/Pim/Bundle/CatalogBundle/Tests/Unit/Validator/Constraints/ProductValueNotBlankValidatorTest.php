<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\FlexibleEntityBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;
use Pim\Bundle\CatalogBundle\Validator\Constraints\ProductValueNotBlank;
use Pim\Bundle\CatalogBundle\Validator\Constraints\ProductValueNotBlankValidator;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
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
        $this->constraint = new ProductValueNotBlank(['channel' => $this->getChannel()]);
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
     * @return array
     */
    public static function dataProviderWithRightSimpleData()
    {
        $attribute = new Attribute();
        $attribute->setCode('price');
        $attribute->setAttributeType('pim_catalog_price_collection')->setBackendType('prices');
        $price = new ProductPrice();
        $price->setCurrency('EUR');
        $price->setData(12.5);

        return [
            ['char' => 'a'],
            ['string' => 'test'],
            ['sentence' => 'juste a sentence'],
            ['integer' => 5],
            ['zero' => 0],
            ['float' => 3.4],
            ['zero float' => 0.0],
            ['negative integer' => -2],
            ['negative float' => -5.3],
            ['negative zero' => -0],
            ['negative zero float' => -0.00],
            ['boolean true' => true],
            ['boolean false' => false],
            ['not empty array' => ['A']],
            ['object' => new \stdClass()],
            ['not empty option collection' => new ArrayCollection([new AttributeOption()])],
            ['expected price collection' => new ArrayCollection([$price]), $attribute],
        ];
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
     * @return array
     */
    public static function dataProviderWithWrongSimpleData()
    {
        $attribute = new Attribute();
        $attribute->setCode('price');
        $attribute->setAttributeType('pim_catalog_price_collection')->setBackendType('prices');
        $price = new ProductPrice();
        $price->setCurrency('EUR');
        $price->setData(null);

        return [
            ['null' => null],
            ['empty string' => ''],
            ['empty option collection' => new ArrayCollection()],
            ['unexpected price collection' => new ArrayCollection([$price]), $attribute],
        ];
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
     * @param mixed     $return
     * @param Attribute $attribute
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
