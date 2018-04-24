<?php

namespace spec\Akeneo\Test\Acceptance\Catalog\Group;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Catalog\Group\InMemoryGroupRepository;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;

class InMemoryGroupRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryGroupRepository::class);
    }

    function it_is_a_group_repository()
    {
        $this->shouldImplement(GroupRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement(SaverInterface::class);
    }

    function it_saves_a_group()
    {
        $this->save(new Group())->shouldReturn(null);
    }

    function it_only_saves_groups()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('save', ['wrong_object']);
    }

    function it_finds_a_group_by_its_identifier()
    {
        $group = new Group();
        $group->setCode('group_code');
        $this->save($group);
        $this->findOneByIdentifier('group_code')->shouldReturn($group);
    }

    function it_returns_null_if_the_group_does_not_exist()
    {
        $this->findOneByIdentifier('group_code')->shouldReturn(null);
    }

    function it_has_identifier_properties()
    {
        $this->getIdentifierProperties()->shouldReturn(['code']);
    }

}
