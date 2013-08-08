<?php

namespace Oro\Bundle\UserBundle\Tests\Entity;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\Acl;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

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

    public function testAcl()
    {
        $aclResource = new Acl();
        $aclResource->setName('test resource');
        $role  = $this->getRole();
        $this->assertEquals(0, $role->getAclResources()->count());
        $role->addAclResource($aclResource);
        $this->assertEquals(1, $role->getAclResources()->count());
        $role->removeAclResource($aclResource);
        $this->assertEquals(0, $role->getAclResources()->count());
        $role->setAclResources(array($aclResource));
        $this->assertEquals(1, count($role->getAclResources()));
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
        $user = new User();
        $businessUnit = new BusinessUnit();
        $organization = new Organization();

        $this->assertEmpty($entity->getUserOwner());
        $this->assertEmpty($entity->getBusinessUnitOwner());
        $this->assertEmpty($entity->getOrganizationOwner());

        $entity->setUserOwner($user);
        $entity->setBusinessUnitOwner($businessUnit);
        $entity->setOrganizationOwner($organization);

        $this->assertEquals($user, $entity->getUserOwner());
        $this->assertEquals($businessUnit, $entity->getBusinessUnitOwner());
        $this->assertEquals($organization, $entity->getOrganizationOwner());
    }
}
