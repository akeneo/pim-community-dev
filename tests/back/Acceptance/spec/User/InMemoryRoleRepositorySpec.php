<?php

namespace spec\Akeneo\Test\Acceptance\User;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\User\InMemoryRoleRepository;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use Prophecy\Argument;

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
        $role->setRole('role');
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
}
