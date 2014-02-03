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
     * @var Attribute
     */
    protected $attributeText;

    /**
     * @var string
     */
    protected $attributeCodeSelect;

    /**
     * @var Attribute
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
        $attributes = array(
            $this->attributeCodeText => $this->attributeText, $this->attributeCodeSelect => $this->attributeSelect
        );
        $this->flexible->setAllAttributes($attributes);
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

        // test magic method
        $this->flexible->setShortDescription('my value');
        $this->assertEquals($this->flexible->getShortDescription(), 'my value');

        $this->flexible->setColor($data2);
        $this->assertEquals($this->flexible->getColor(), $value2);
    }

    /**
     * Test related method
     */
    public function testValuesWithScopeLocale()
    {
        $attribute = new Attribute();
        $attribute->setCode($this->attributeCodeText);
        $attribute->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);

        $localeAttribute = new Attribute();
        $localeAttribute->setLocalizable(true);
        $localeAttribute->setCode($this->attributeCodeText.'Localizable');
        $localeAttribute->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);

        $scopeAttribute = new Attribute();
        $scopeAttribute->setScopable(true);
        $scopeAttribute->setCode($this->attributeCodeText.'Scopable');
        $scopeAttribute->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);

        $localeScopeAttribute = new Attribute();
        $localeScopeAttribute->setLocalizable(true);
        $localeScopeAttribute->setScopable(true);
        $localeScopeAttribute->setCode($this->attributeCodeText.'LocalizableScopable');
        $localeScopeAttribute->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);

        $this->flexible->setAllAttributes(
            array_merge(
                $this->flexible->getAllAttributes(),
                array(
                    $localeAttribute->getCode() => $localeAttribute,
                    $scopeAttribute->getCode()  => $scopeAttribute,
                    $localeScopeAttribute->getCode() => $localeScopeAttribute
                )
            )
        );

        $valuesData = array(
            array('attr' => $attribute, 'locale' => null, 'scope' => null, 'data' => 'My non localizable non scopable value'),
            array('attr' => $localeAttribute, 'locale' => 'en_US', 'scope' => null, 'data' => 'My en_US value'),
            array('attr' => $localeAttribute, 'locale' => 'fr_FR', 'scope' => null, 'data' => 'My fr_FR value'),
            array('attr' => $scopeAttribute, 'locale' => null, 'scope' => 'ecommerce', 'data' => 'My ecommerce value'),
            array('attr' => $scopeAttribute, 'locale' => null, 'scope' => 'mobile', 'data' => 'My mobile value'),
            array('attr' => $localeScopeAttribute, 'locale' => 'en_US', 'scope' => 'ecommerce', 'data' => 'My en_US ecommerce value'),
            array('attr' => $localeScopeAttribute, 'locale' => 'en_US', 'scope' => 'mobile', 'data' => 'My en_US mobile value'),
            array('attr' => $localeScopeAttribute, 'locale' => 'fr_FR', 'scope' => 'ecommerce', 'data' => 'My fr_FR ecommerce value'),
            array('attr' => $localeScopeAttribute, 'locale' => 'fr_FR', 'scope' => 'mobile', 'data' => 'My fr_FR mobile value'),
            array('attr' => $localeScopeAttribute, 'locale' => null, 'scope' => 'ecommerce', 'data' => 'My no locale ecommerce value'),
            array('attr' => $localeScopeAttribute, 'locale' => 'en_US', 'scope' => null, 'data' => 'My en_US no scope value'),
            array('attr' => $localeScopeAttribute, 'locale' => null, 'scope' => null, 'data' => 'My no locale no scope value'),
        );

        $values = array();

        foreach($valuesData as $valueData) {
            $value = new FlexibleValue();
            $value->setAttribute($valueData['attr']);
            $value->setLocale($valueData['locale']);
            $value->setScope($valueData['scope']);
            $value->setData($valueData['data']);
            $this->flexible->addValue($value);
            $values[] = array('value' => $value, 'data' => $valueData);
        }

        foreach($values as $value) {
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

        foreach($enEcomValues as $value) {
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
