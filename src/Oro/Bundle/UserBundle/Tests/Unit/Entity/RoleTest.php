<?php

namespace Oro\Bundle\UserBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\UserBundle\Entity\Role;

class RoleTest extends \PHPUnit_Framework_TestCase
{
    public function testRole()
    {
        $role = $this->getRole();

        $this->assertEmpty($role->getId());
        $this->assertEmpty($role->getRole());

        $role->setRole('foo');

        $this->assertEquals('ROLE_FOO', $role->getRole());
        $this->assertEquals('ROLE_FOO', $role);
        $this->assertNotEquals('foo', $role);
    }

    public function testLabel()
    {
        $role  = $this->getRole();
        $label = 'Test role';

        $this->assertEmpty($role->getLabel());

        $role->setLabel($label);

        $this->assertEquals($label, $role->getLabel());
    }

    protected function setUp()
    {
        $this->role = new Role();
    }

    /**
     * @return Role
     */
    protected function getRole()
    {
        return $this->role;
    }
}
