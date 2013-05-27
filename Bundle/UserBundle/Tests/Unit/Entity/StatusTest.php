<?php

namespace Oro\Bundle\UserBundle\Tests\Unit\Entity;

use Oro\Bundle\UserBundle\Entity\Status;

class StatusTest extends \PHPUnit_Framework_TestCase
{
    private $user;

    private $status;

    public function setUp()
    {
        $this->user = $this->getMockForAbstractClass('Oro\Bundle\UserBundle\Entity\User');
        $this->status = new Status();
    }

    public function testId()
    {
        $this->assertNull($this->status->getId());
    }

    public function testStatus()
    {
        $statusString = 'test status';
        $this->assertNull($this->status->getStatus());
        $this->status->setStatus($statusString);
        $this->assertEquals($statusString, $this->status->getStatus());
    }

    public function testUser()
    {
        $this->assertNull($this->status->getUser());
        $this->status->setUser($this->user);
        $this->assertEquals($this->user, $this->status->getUser());
    }

    public function testCreatedAt()
    {
        $this->assertNull($this->status->getCreatedAt());
        $this->status->setCreatedAt(new \DateTime('2013-01-01'));
        $this->assertEquals('2013-01-01', $this->status->getCreatedAt()->format('Y-m-d'));
    }

    public function testBeforeSave()
    {
        $this->assertNull($this->status->getCreatedAt());
        $this->status->beforeSave();
        $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->assertEquals($currentDate->format('Y-m-d'), $this->status->getCreatedAt()->format('Y-m-d'));
    }
}
