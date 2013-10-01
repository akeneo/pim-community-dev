<?php

namespace Oro\Bundle\UserBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\UserBundle\Entity\Role;

use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    const TEST_ROLE = 'ROLE_FOO';

    /**
     * @var Group
     */
    protected $group;

    protected function setUp()
    {
        $this->group = new Group();
    }

    public function testGroup()
    {
        $name  = 'Users';

        $this->assertEmpty($this->group->getId());
        $this->assertEmpty($this->group->getName());

        $this->group->setName($name);

        $this->assertEquals($name, $this->group->getName());
    }

    public function testGetRoleLabelsAsString()
    {
        $roleFoo  = new Role('ROLE_FOO');
        $roleFoo->setLabel('Role foo');
        $this->group->addRole($roleFoo);

        $roleBar  = new Role('ROLE_BAR');
        $roleBar->setLabel('Role bar');
        $this->group->addRole($roleBar);

        $this->assertEquals(
            'Role foo, Role bar',
            $this->group->getRoleLabelsAsString()
        );
    }

    public function testHasRoleWithStringArgument()
    {
        $role = new Role(self::TEST_ROLE);

        $this->assertFalse($this->group->hasRole(self::TEST_ROLE));
        $this->group->addRole($role);
        $this->assertTrue($this->group->hasRole(self::TEST_ROLE));
    }

    public function testHasRoleWithObjectArgument()
    {
        $role = new Role(self::TEST_ROLE);

        $this->assertFalse($this->group->hasRole($role));
        $this->group->addRole($role);
        $this->assertTrue($this->group->hasRole($role));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $role must be an instance of Oro\Bundle\UserBundle\Entity\Role or a string
     */
    public function testHasRoleThrowsInvalidArgumentException()
    {
        $this->group->hasRole(new \stdClass());
    }

    public function testRemoveRoleWithStringArgument()
    {
        $role = new Role(self::TEST_ROLE);
        $this->group->addRole($role);

        $this->assertTrue($this->group->hasRole($role));
        $this->group->removeRole(self::TEST_ROLE);
        $this->assertFalse($this->group->hasRole($role));
    }

    public function testRemoveRoleWithObjectArgument()
    {
        $role = new Role(self::TEST_ROLE);
        $this->group->addRole($role);

        $this->assertTrue($this->group->hasRole($role));
        $this->group->removeRole($role);
        $this->assertFalse($this->group->hasRole($role));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $role must be an instance of Oro\Bundle\UserBundle\Entity\Role or a string
     */
    public function testRemoveRoleThrowsInvalidArgumentException()
    {
        $this->group->removeRole(new \stdClass());
    }

    public function testSetRolesWithArrayArgument()
    {
        $roles = array(new Role(self::TEST_ROLE));
        $this->assertCount(0, $this->group->getRoles());
        $this->group->setRoles($roles);
        $this->assertEquals($roles, $this->group->getRoles()->toArray());
    }

    public function testSetRolesWithCollectionArgument()
    {
        $roles = new ArrayCollection(array(new Role(self::TEST_ROLE)));
        $this->assertCount(0, $this->group->getRoles());
        $this->group->setRoles($roles);
        $this->assertEquals($roles->toArray(), $this->group->getRoles()->toArray());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $roles must be an instance of Doctrine\Common\Collections\Collection or an array
     */
    public function testSetRolesThrowsInvalidArgumentException()
    {
        $this->group->setRoles('roles');
    }

    public function testOwners()
    {
        $entity = $this->group;
        $businessUnit = new BusinessUnit();

        $this->assertEmpty($entity->getOwner());

        $entity->setOwner($businessUnit);

        $this->assertEquals($businessUnit, $entity->getOwner());
    }
}
