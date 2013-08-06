<?php

namespace Oro\Bundle\AddressBundle\Tests\Entity;

use Oro\Bundle\AddressBundle\Entity\AbstractEmail;

class AbstractEmailTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractEmail
     */
    protected $email;

    protected function setUp()
    {
        $this->email = $this->getMockForAbstractClass('Oro\Bundle\AddressBundle\Entity\AbstractEmail');
    }

    protected function tearDown()
    {
        unset($this->email);
    }

    public function testEmail()
    {
        $this->assertNull($this->email->getEmail());
        $this->email->setEmail('email@example.com');
        $this->assertEquals('email@example.com', $this->email->getEmail());
    }

    public function testToString()
    {
        $this->assertEquals('', (string)$this->email);
        $this->email->setEmail('email@example.com');
        $this->assertEquals('email@example.com', (string)$this->email);
    }

    public function testPrimary()
    {
        $this->assertFalse($this->email->isPrimary());
        $this->email->setPrimary(true);
        $this->assertTrue($this->email->isPrimary());
    }
}
