<?php
namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity;

use Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\FlexibleValue;

use Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\Flexible;
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
    public function setUp()
    {
        $this->attributeCodeText = 'short_description';
        $this->attributeText = new Attribute();
        $this->attributeText->setCode($this->attributeCodeText);
        $this->attributeText->setBackendType('varchar');

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
}
