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
        $this->email = $this->createEmail();
    }

    protected function tearDown()
    {
        unset($this->email);
    }

    public function testConstructor()
    {
        $this->email = $this->createEmail('email@example.com');

        $this->assertEquals('email@example.com', $this->email->getEmail());
    }

    public function testId()
    {
        $this->assertNull($this->email->getId());
        $this->email->setId(100);
        $this->assertEquals(100, $this->email->getId());
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

    /**
     * @param string|null $email
     * @return AbstractEmail|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createEmail($email = null)
    {
        $arguments = array();
        if ($email) {
            $arguments[] = $email;
        }
        return $this->getMockForAbstractClass('Oro\Bundle\AddressBundle\Entity\AbstractEmail', $arguments);
    }
}
