<?php

declare(strict_types=1);

namespace Specification\Akeneo\Test\Category\Acceptance\InMemory;

use Akeneo\Pim\Enrichment\Category\API\Query\GetOwnedCategories;
use Akeneo\Test\Acceptance\User\InMemoryUserRepository;
use Akeneo\Test\Category\Acceptance\InMemory\InMemoryGetOwnedCategories;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;

class InMemoryGetOwnedCategoriesSpec extends ObjectBehavior
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
        $this->shouldBeAnInstanceOf(InMemoryGetOwnedCategories::class);
        $this->shouldImplement(GetOwnedCategories::class);
    }

    function it_returns_owned_category_codes()
    {
        $this->addOwnedCategoryCode('admin', 'master');
        $this->addOwnedCategoryCode('all', 'master');
        $this->addOwnedCategoryCode('admin', 'print');

        $this->forUserId(['master', 'print', 'unknown'], 1)->shouldReturn([]);
        $this->forUserId(['master', 'print', 'unknown'], 2)->shouldReturn(['master', 'print']);
        $this->forUserId(['master', 'print', 'unknown'], 3)->shouldReturn(['master']);
        $this->forUserId(['master', 'print', 'unknown'], 99)->shouldReturn([]);

        $this->forUserId(['print'], 2)->shouldReturn(['print']);
        $this->forUserId([], 2)->shouldReturn([]);

    }
}
