<?php

namespace spec\Pim\Component\Catalog\Factory;

use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\GroupTypeInterface;
use Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface;

class GroupFactorySpec extends ObjectBehavior
{
    const GROUP_CLASS = 'Akeneo\Pim\Enrichment\Component\Product\Model\Group';

    function let(GroupTypeRepositoryInterface $groupTypeRepository)
    {
        $this->beConstructedWith($groupTypeRepository, self::GROUP_CLASS);
    }

    function it_creates_a_group()
    {
        $this->createGroup()->shouldReturnAnInstanceOf(self::GROUP_CLASS);
    }

    function it_creates_a_group_with_a_type($groupTypeRepository, GroupTypeInterface $groupType)
    {
        $groupTypeRepository->findOneByIdentifier('VARIANT')->willReturn($groupType);
        $this->createGroup('VARIANT')->shouldReturnAnInstanceOf(self::GROUP_CLASS);
    }

    function it_throws_an_exception_if_no_group_types_are_found($groupTypeRepository)
    {
        $groupTypeRepository->findOneByIdentifier('INVALID_GROUP_TYPE_CODE')->willReturn(null);

        $this->shouldThrow(
            new \InvalidArgumentException(sprintf('Group type with code "%s" was not found', 'INVALID_GROUP_TYPE_CODE'))
        )->during('createGroup', ['INVALID_GROUP_TYPE_CODE']);
    }
}
