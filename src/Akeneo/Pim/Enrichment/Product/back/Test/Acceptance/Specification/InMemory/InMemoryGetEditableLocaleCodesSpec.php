<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\Test\Acceptance\InMemory;

use Akeneo\Channel\Locale\API\Query\GetEditableLocaleCodes;
use Akeneo\Pim\Enrichment\Product\Test\Acceptance\InMemory\InMemoryGetEditableLocaleCodes;
use Akeneo\Test\Acceptance\User\InMemoryUserRepository;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;

class InMemoryGetEditableLocaleCodesSpec extends ObjectBehavior
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
        $this->shouldHaveType(InMemoryGetEditableLocaleCodes::class);
        $this->shouldImplement(GetEditableLocaleCodes::class);
    }

    function it_returns_all_activated_locale_codes()
    {
        $this->forUserId(1)->shouldReturn([]);
        $this->forUserId(2)->shouldReturn([]);
        $this->forUserId(3)->shouldReturn([]);

        $this->addOwnedCategoryCode('admin', 'fr_FR');
        $this->addOwnedCategoryCode('admin', 'en_US');
        $this->forUserId(1)->shouldReturn([]);
        $this->forUserId(2)->shouldReturn(['fr_FR', 'en_US']);
        $this->forUserId(3)->shouldReturn([]);
    }
}
