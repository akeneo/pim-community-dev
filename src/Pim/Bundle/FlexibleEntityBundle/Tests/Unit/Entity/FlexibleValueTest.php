<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity;

use Pim\Bundle\FlexibleEntityBundle\Entity\Media;

use Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\FlexibleValue;
use Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\Flexible;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Pim\Bundle\FlexibleEntityBundle\Entity\AttributeOption;
use Pim\Bundle\FlexibleEntityBundle\Entity\Metric;
use Pim\Bundle\FlexibleEntityBundle\Entity\Price;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Test related demo class, aims to cover abstract one
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleValueTest extends \PHPUnit_Framework_TestCase
{
    protected $flexible;

    protected $attribute;

    protected $value;

    /**
     * Set up unit test
     */
    public function setUp()
    {
        // create flexible
        $this->flexible = new Flexible();
        // create attribute
        $this->attribute = new Attribute();
        $this->attribute->setCode('mycode');
        $this->attribute->setTranslatable(true);
        $this->attribute->setScopable(true);
        $this->attribute->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);
        // create value
        $this->value = new FlexibleValue();
        $this->value->setAttribute($this->attribute);
        $this->value->setEntity($this->flexible);
    }

    /**
     * Test related method
     */
    public function testGetId()
    {
        $this->assertNull($this->value->getId());
    }

    /**
     * Test related method
     */
    public function testGetAttribute()
    {
        $this->assertEquals($this->value->getAttribute(), $this->attribute);
    }

    /**
     * Test related method
     */
    public function testGetLocale()
    {
        $code = 'fr';
        $this->value->setLocale($code);
        $this->assertEquals($this->value->getLocale(), $code);
    }

    /**
     * Test related method
     */
    public function testGetScope()
    {
        $code = 'ecommerce';
        $this->value->setScope($code);
        $this->assertEquals($this->value->getScope(), $code);
    }

    /**
     * Test related method
     *
     * @param string $backendType the attribute backend type
     * @param mixed  $data        the value data
     *
     * @dataProvider valueProvider
     */
    public function testGetData($backendType, $data)
    {
        $this->value->getAttribute()->setBackendType($backendType);

        if ($this->value->getData() instanceof ArrayCollection) {
            $this->assertEquals($this->value->getData()->count(), 0);
        } else {
            $this->assertNull($this->value->getData());
        }

        $this->value->setData($data);
        if ($this->value->getData() instanceof ArrayCollection) {
            $this->assertEquals($this->value->getData()->count(), 1);
        } else {
            $this->assertEquals($this->value->getData(), $data);
        }

        $this->assertTrue(strlen($this->value->__toString()) >= 0);
    }

    /**
     * Data provider
     *
     * @return multitype:multitype:number string
     *
     * @static
     */
    public static function valueProvider()
    {
        $options = new ArrayCollection();
        $option  = new AttributeOption();
        $options->add($option);
        $price = new Price();
        $price->setData(5)->setCurrency('USD');
        $metric = new Metric();
        $metric->setData(12.5)->setUnit('km');

        return array(
            array(AbstractAttributeType::BACKEND_TYPE_TEXT, 'my really loooonnnng text'),
            array(AbstractAttributeType::BACKEND_TYPE_VARCHAR, 'my value'),
            array(AbstractAttributeType::BACKEND_TYPE_INTEGER, 12),
            array(AbstractAttributeType::BACKEND_TYPE_DECIMAL, 123.45),
            array(AbstractAttributeType::BACKEND_TYPE_DATE, new \DateTime()),
            array(AbstractAttributeType::BACKEND_TYPE_DATETIME, new \DateTime()),
            array(AbstractAttributeType::BACKEND_TYPE_OPTION, $option),
            array(AbstractAttributeType::BACKEND_TYPE_OPTIONS, $options),
            array(AbstractAttributeType::BACKEND_TYPE_MEDIA, new Media()),
            array(AbstractAttributeType::BACKEND_TYPE_PRICE, $price),
            array(AbstractAttributeType::BACKEND_TYPE_METRIC, $metric),
        );
    }

    /**
     * Test related method
     */
    public function testGetUnit()
    {
        $data = 5;
        $unit = 'mm';
        $metric = new Metric();
        $metric->setUnit($unit);
        $metric->setData($data);
        $this->value->setData($metric);
        $this->assertEquals($this->value->getData()->getUnit(), $unit);
        $this->assertEquals($this->value->getData()->getData(), $data);
    }

    /**
     * Test related method
     */
    public function testGetCurrency()
    {
        $data = 5;
        $currency = 'USD';
        $price = new Price();
        $price->setData($data);
        $price->setCurrency($currency);
        $this->value->setData($price);
        $this->assertEquals($this->value->getData()->getCurrency(), $currency);
        $this->assertEquals($this->value->getData()->getData(), $data);
    }

    /**
     * Test related method
     */
    public function testGetOption()
    {
        $option = new AttributeOption();
        $this->value->setOption($option);
        $this->assertEquals($this->value->getOption(), $option);
    }
}
