<?php

namespace Oro\Bundle\UserBundle\Tests\Unit\Entity;

use Oro\Bundle\UserBundle\Entity\Email;

class EmailTest extends \PHPUnit_Framework_TestCase
{
    private $user;

    private $email;

    public function setUp()
    {
        $this->user = $this->getMockForAbstractClass('Oro\Bundle\UserBundle\Entity\User');
        $this->email = new Email();
    }

    public function testEmail()
    {
        $email = 'email@example.com';
        $this->assertNull($this->email->getEmail());
        $this->email->setEmail($email);
        $this->assertEquals($email, $this->email->getEmail());
    }

    public function testId()
    {
        $this->assertNull($this->email->getId());
    }

    public function testUser()
    {
        $this->assertNull($this->email->getUser());
        $this->email->setUser($this->user);
        $this->assertEquals($this->user, $this->email->getUser());
    }
}
