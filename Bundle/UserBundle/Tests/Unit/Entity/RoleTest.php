<?php

namespace Oro\Bundle\UserBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\UserBundle\Entity\Role;

use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;

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

    public function testOwners()
    {
        $entity = $this->getRole();
        $businessUnit = new BusinessUnit();

        $this->assertEmpty($entity->getOwner());

        $entity->setOwner($businessUnit);

        $this->assertEquals($businessUnit, $entity->getOwner());
    }
}
