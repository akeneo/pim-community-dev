<?php

declare(strict_types=1);

namespace Specification\Akeneo\Test\Channel\Acceptance\InMemory;

use Akeneo\Channel\API\Query\IsLocaleEditable;
use Akeneo\Test\Acceptance\User\InMemoryUserRepository;
use Akeneo\Test\Channel\Acceptance\InMemory\InMemoryIsLocaleEditable;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;

class InMemoryIsLocaleEditableSpec extends ObjectBehavior
{
    function let()
    {
        $userRepository = new InMemoryUserRepository();

        $adminGroup = new Group('admin');
        $allGroup = new Group('all');

        $noGroupUser = new User();
        $noGroupUser->setId(1);
        $noGroupUser->setUsername('no_group_user');
        $userRepository->save($noGroupUser);

        $adminUser = new User();
        $adminUser->setId(2);
        $adminUser->setUsername('admin_user');
        $adminUser->addGroup($adminGroup);
        $adminUser->addGroup($allGroup);
        $userRepository->save($adminUser);

        $basicUser = new User();
        $basicUser->setId(3);
        $basicUser->setUsername('basic_user');
        $basicUser->addGroup($allGroup);
        $userRepository->save($basicUser);

        $this->beConstructedWith($userRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryIsLocaleEditable::class);
        $this->shouldImplement(IsLocaleEditable::class);
    }

    function it_returns_all_activated_locale_codes()
    {
        $this->addEditableLocaleCodeForGroup('admin', 'en_US');

        $this->forUserId('en_US', 1)->shouldReturn(false);
        $this->forUserId('fr_FR', 1)->shouldReturn(false);
        $this->forUserId('en_US', 2)->shouldReturn(true);
        $this->forUserId('fr_FR', 2)->shouldReturn(false);
        $this->forUserId('en_US', 3)->shouldReturn(false);
        $this->forUserId('fr_FR', 3)->shouldReturn(false);
        $this->forUserId('en_US', 99)->shouldReturn(false);
    }
}
