<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\GroupsUserIntentFactory;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class GroupsUserIntentFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(GroupsUserIntentFactory::class);
    }

    function it_returns_set_groups_user_intent()
    {
        $this->create('groups', ['group1'])->shouldBeLike([new SetGroups(['group1'])]);
    }

    function it_returns_empty_set_groups_user_intent()
    {
        $this->create('groups', [])->shouldBeLike([new SetGroups([])]);
    }

    function it_throws_an_exception_if_data_is_not_valid()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', ['groups', 12]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', ['groups', null]);
    }
}
