<?php

namespace Oro\Bundle\AddressBundle\Tests\Entity;

use Oro\Bundle\AddressBundle\Entity\AbstractPhone;

class AbstractPhoneTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractPhone
     */
    protected $phone;

    protected function setUp()
    {
        $this->phone = $this->getMockForAbstractClass('Oro\Bundle\AddressBundle\Entity\AbstractPhone');
    }

    protected function tearDown()
    {
        unset($this->phone);
    }

    public function testPhone()
    {
        $this->assertNull($this->phone->getPhone());
        $this->phone->setPhone('080011223355');
        $this->assertEquals('080011223355', $this->phone->getPhone());
    }

    public function testToString()
    {
        $this->assertEquals('', (string)$this->phone);
        $this->phone->setPhone('080011223355');
        $this->assertEquals('080011223355', (string)$this->phone);
    }

    public function testPrimary()
    {
        $this->assertFalse($this->phone->isPrimary());
        $this->phone->setPrimary(true);
        $this->assertTrue($this->phone->isPrimary());
    }
}
