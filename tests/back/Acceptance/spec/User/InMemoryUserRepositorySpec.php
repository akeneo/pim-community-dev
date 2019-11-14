<?php

namespace spec\Akeneo\Test\Acceptance\User;

use Akeneo\Test\Acceptance\User\InMemoryUserRepository;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;

class InMemoryUserRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryUserRepository::class);
    }

    function it_is_a_user_repository()
    {
        $this->shouldImplement(UserRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement(SaverInterface::class);
    }

    function it_saves_a_user()
    {
        $this->save(new User())->shouldReturn(null);
    }

    function it_only_saves_users()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('save', ['wrong_object']);
    }

    function it_finds_a_user_by_its_identifier()
    {
        $user = new User();
        $user->setUsername('username');
        $this->save($user);
        $this->findOneByIdentifier('username')->shouldReturn($user);
    }

    function it_returns_null_if_the_user_does_not_exist()
    {
        $this->findOneByIdentifier('username')->shouldReturn(null);
    }

    function it_finds_a_user_by_username()
    {
        $user = new User();
        $user->setUsername('julia');
        $this->save($user);
        $this->findOneBy(['username' => 'julia'])->shouldReturn($user);
    }

    function it_returns_null_if_the_username_does_not_belong_to_a_user()
    {
        $this->findOneBy(['username' => 'unknown'])->shouldReturn(null);
    }

    function it_throws_if_the_criteria_is_not_username()
    {
        $this->shouldThrow(new \InvalidArgumentException('This method only supports finding by "username"'))
            ->during('findOneBy', [['role' => 'admin']]);
    }

    function it_has_identifier_properties()
    {
        $this->getIdentifierProperties()->shouldReturn(['username']);
    }

    function it_finds_users_by_criteria()
    {
        $user = new User();
        $user->setUsername('mary');
        $this->save($user);

        $this->findBy(['username' => 'mary'])->shouldReturn([$user]);
    }

    function it_finds_a_user_by_its_id()
    {
        $user = new User();
        $user->setUsername('mary');
        $user->setId(42);
        $this->save($user);

        $this->find(42)->shouldReturn($user);
    }

    function it_does_not_find_users_by_criteria()
    {
        $user = new User();
        $user->setUsername('mary');
        $this->save($user);

        $this->findBy(['username' => 'julia'])->shouldReturn([]);
    }
}
