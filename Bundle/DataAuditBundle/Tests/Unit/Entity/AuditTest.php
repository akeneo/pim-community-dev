<?php

namespace Oro\Bundle\DataAuditBundle\Tests\Unit\Entity;

use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Oro\Bundle\UserBundle\Entity\User;

class AuditTest extends \PHPUnit_Framework_TestCase
{
    public function testUser()
    {
        $user  = new User();
        $audit = new Audit();

        $this->assertEmpty($audit->getUser());

        $audit->setUser($user);

        $this->assertNotEmpty($audit->getUser());
    }

    public function testObjectName()
    {
        $audit = new Audit();
        $name  = 'LoggedObject';

        $this->assertEmpty($audit->getObjectName());

        $audit->setObjectName($name);

        $this->assertEquals($name, $audit->getObjectName());
    }
}
