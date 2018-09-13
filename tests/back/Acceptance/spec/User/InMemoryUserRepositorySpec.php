<?php

namespace spec\Akeneo\Test\Acceptance\User;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\User\InMemoryUserRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\User;
use Pim\Bundle\UserBundle\Repository\UserRepositoryInterface;
use Prophecy\Argument;

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

    function it_does_not_find_users_by_criteria()
    {
        $user = new User();
        $user->setUsername('mary');
        $this->save($user);

        $this->findBy(['username' => 'julia'])->shouldReturn([]);
    }
}
