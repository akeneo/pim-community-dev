<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests\Unit\Entity;

use Oro\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\FlexibleValue;

use Oro\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\Flexible;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;

/**
 * Test related demo class, aims to cover abstract one
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FlexibleTest extends \PHPUnit_Framework_TestCase
{

    protected $flexible;

    /**
     * Set up unit test
     */
    public function setUp()
    {
        // create flexible
        $this->flexible = new Flexible();
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
        $code = 'fr_FR';
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
        // create attribute
        $att = new Attribute();
        $code = 'mycode';
        $att->setCode($code);
        $att->setBackendType('varchar');
        // create value
        $data = 'my test value';
        $value = new FlexibleValue();
        $value->setAttribute($att);
        $value->setData($data);
        // get / add / remove values
        $this->assertEquals($this->flexible->getValues()->count(), 0);
        $this->flexible->addValue($value);
        $this->assertEquals($this->flexible->getValues()->count(), 1);
        $this->assertEquals($this->flexible->getValue($code), $value);
        $this->assertEquals($this->flexible->mycode, $data);
        $this->flexible->removeValue($value);
        $this->assertEquals($this->flexible->getValues()->count(), 0);
    }
}
