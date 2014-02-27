<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity;

use Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\FlexibleValue;

use Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\Flexible;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Pim\Bundle\FlexibleEntityBundle\Entity\AttributeOption;

/**
 * Test related demo class, aims to cover abstract one
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Flexible
     */
    protected $flexible;

    /**
     * @var string
     */
    protected $attributeCodeText;

    /**
     * @var \Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute
     */
    protected $attributeText;

    /**
     * @var string
     */
    protected $attributeCodeSelect;

    /**
     * @var \Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute
     */
    protected $attributeSelect;

    /**
     * Set up unit test
     */
    protected function setUp()
    {
        $this->attributeCodeText = 'short_description';
        $this->attributeText = new Attribute();
        $this->attributeText->setCode($this->attributeCodeText);
        $this->attributeText->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);

        $this->attributeCodeSelect = 'color';
        $this->attributeSelect = new Attribute();
        $this->attributeSelect->setCode($this->attributeCodeSelect);
        $this->attributeSelect->setBackendType('options');

        $this->flexible = new Flexible();
        $this->flexible->setValueClass('Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\FlexibleValue');
    }

    /**
     * Test related method
     */
    public function testMyField()
    {
        $myfield = 'my field';
        $this->flexible->setMyfield($myfield);
        $this->assertEquals($this->flexible->getMyfield(), $myfield);
    }

    /**
     * Test related method
     */
    public function testGetLocale()
    {
        $code = 'fr';
        $this->flexible->setLocale($code);
        $this->assertEquals($this->flexible->getLocale(), $code);
    }

    /**
     * Test related method
     */
    public function testGetScope()
    {
        $code = 'mobile';
        $this->flexible->setScope($code);
        $this->assertEquals($this->flexible->getScope(), $code);
    }

    /**
     * Test related method
     */
    public function testGetId()
    {
        $this->assertNull($this->flexible->getId());
    }

    /**
     * Test related method
     */
    public function testUpdated()
    {
        $date = new \DateTime();
        $this->flexible->setUpdated($date);
        $this->assertEquals($this->flexible->getUpdated(), $date);
    }

    /**
     * Test related method
     */
    public function testCreated()
    {
        $date = new \DateTime();
        $this->flexible->setCreated($date);
        $this->assertEquals($this->flexible->getCreated(), $date);
    }

    /**
     * Test related method
     */
    public function testValues()
    {
        $data = 'my test value';
        $value = new FlexibleValue();
        $value->setAttribute($this->attributeText);
        $value->setData($data);

        $data2 = new AttributeOption();
        $value2 = new FlexibleValue();
        $value2->setAttribute($this->attributeSelect);
        $value2->setData($data2);

        // get / add / remove values
        $this->assertEquals($this->flexible->getValues()->count(), 0);
        $this->flexible->addValue($value);

        $this->assertEquals($this->flexible->getValues()->count(), 1);
        $this->assertEquals($this->flexible->getValue($this->attributeCodeText), $value);
        $this->assertEquals($this->flexible->short_description, $data);

        $this->flexible->addValue($value2);
        $this->assertEquals($this->flexible->getValues()->count(), 2);
        $this->assertEquals($this->flexible->getValue($this->attributeCodeSelect), $value2);
        $this->assertEquals($this->flexible->color, $value2);

        $this->flexible->removeValue($value);
        $this->assertEquals($this->flexible->getValues()->count(), 1);
        $this->flexible->removeValue($value2);
        $this->assertEquals($this->flexible->getValues()->count(), 0);
    }

    /**
     * Test related method
     */
    public function testValuesWithScopeLocale()
    {
        $attr= new Attribute();
        $attr->setCode($this->attributeCodeText);
        $attr->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);

        $lAttr = new Attribute();
        $lAttr->setLocalizable(true);
        $lAttr->setCode($this->attributeCodeText.'Localizable');
        $lAttr->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);

        $sAttr = new Attribute();
        $sAttr->setScopable(true);
        $sAttr->setCode($this->attributeCodeText.'Scopable');
        $sAttr->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);

        $lsAttr = new Attribute();
        $lsAttr->setLocalizable(true);
        $lsAttr->setScopable(true);
        $lsAttr->setCode($this->attributeCodeText.'LocalizableScopable');
        $lsAttr->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);

        $valuesData = array(
            array('attr' => $attr, 'locale' => null, 'scope' => null, 'data' => 'Non localizable non scopable value'),
            array('attr' => $lAttr, 'locale' => 'en_US', 'scope' => null, 'data' => 'en_US value'),
            array('attr' => $lAttr, 'locale' => 'fr_FR', 'scope' => null, 'data' => 'fr_FR value'),
            array('attr' => $sAttr, 'locale' => null, 'scope' => 'ecommerce', 'data' => 'ecommerce value'),
            array('attr' => $sAttr, 'locale' => null, 'scope' => 'mobile', 'data' => 'mobile value'),
            array('attr' => $lsAttr, 'locale' => 'en_US', 'scope' => 'ecommerce', 'data' => 'en_US ecommerce value'),
            array('attr' => $lsAttr, 'locale' => 'en_US', 'scope' => 'mobile', 'data' => 'en_US mobile value'),
            array('attr' => $lsAttr, 'locale' => 'fr_FR', 'scope' => 'ecommerce', 'data' => 'fr_FR ecommerce value'),
            array('attr' => $lsAttr, 'locale' => 'fr_FR', 'scope' => 'mobile', 'data' => 'fr_FR mobile value'),
            array('attr' => $lsAttr, 'locale' => null, 'scope' => 'ecommerce', 'data' => 'no locale ecommerce value'),
            array('attr' => $lsAttr, 'locale' => 'en_US', 'scope' => null, 'data' => 'en_US no scope value'),
            array('attr' => $lsAttr, 'locale' => null, 'scope' => null, 'data' => 'no locale no scope value'),
        );

        $values = array();

        foreach ($valuesData as $valueData) {
            $value = new FlexibleValue();
            $value->setAttribute($valueData['attr']);
            $value->setLocale($valueData['locale']);
            $value->setScope($valueData['scope']);
            $value->setData($valueData['data']);
            $this->flexible->addValue($value);
            $values[] = array('value' => $value, 'data' => $valueData);
        }

        foreach ($values as $value) {
            $attribute = $value['data']['attr'];
            $locale = $value['data']['locale'];
            $scope = $value['data']['scope'];

            $this->assertEquals(
                $this->flexible->getValue($attribute->getCode(), $locale, $scope),
                $value['value']
            );
        }

        $enEcomValues = array(
            $values[0],
            $values[1],
            $values[3],
            $values[5]
        );

        $this->flexible->setLocale('en_US');
        $this->flexible->setScope('ecommerce');

        foreach ($enEcomValues as $value) {
            $attribute = $value['data']['attr'];

            $this->assertEquals(
                $this->flexible->getValue($attribute->getCode()),
                $value['value']
            );
        }
    }

    public function testHasAttribute()
    {
        $value = new FlexibleValue();
        $value->setAttribute($this->attributeText);
        $this->flexible->addValue($value);

        $this->assertTrue($this->flexible->hasAttribute($this->attributeText));
        $this->assertFalse($this->flexible->hasAttribute($this->attributeSelect));
    }
}
