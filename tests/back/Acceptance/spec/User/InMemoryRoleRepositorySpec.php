<?php

namespace spec\Akeneo\Test\Acceptance\User;

use Akeneo\Test\Acceptance\User\InMemoryRoleRepository;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use PhpSpec\ObjectBehavior;

class InMemoryRoleRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryRoleRepository::class);
    }

    function it_is_a_role_repository()
    {
        $this->shouldImplement(RoleRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement(SaverInterface::class);
    }

    function it_saves_a_role()
    {
        $this->save(new Role())->shouldReturn(null);
    }

    function it_only_saves_roles()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('save', ['wrong_object']);
    }

    function it_finds_a_role_by_its_identifier()
    {
        $role = new Role();
        $role->setRole('ROLE_ROLE');
        $this->save($role);
        $this->findOneByIdentifier('ROLE_ROLE')->shouldReturn($role);
    }

    function it_returns_null_if_the_role_does_not_exist()
    {
        $this->findOneByIdentifier('role')->shouldReturn(null);
    }

    function it_has_identifier_properties()
    {
        $this->getIdentifierProperties()->shouldReturn(['role']);
    }

    function it_returns_all_roles()
    {
        $role1 = new Role();
        $role1->setRole('ROLE_ROLE1');
        $this->save($role1);

        $role2 = new Role();
        $role2->setRole('ROLE_ROLE2');
        $this->save($role2);

        $this->findAll()->shouldReturn(['ROLE_ROLE1' => $role1, 'ROLE_ROLE2' => $role2]);
    }
}
