<?php

namespace spec\Akeneo\Test\Acceptance\Catalog;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Catalog\InMemoryGroupRepository;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Repository\GroupRepositoryInterface;

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

    function it_finds_all_groups()
    {
        $group = new Group();
        $group->setCode('group_code');
        $this->save($group);

        $group2 = new Group();
        $group2->setCode('group_code_2');
        $this->save($group2);

        $this->findAll()->shouldReturn(['group_code' => $group, 'group_code_2' => $group2]);
    }

    function it_finds_attribute_group_by_criteria()
    {
        $group = new Group();
        $group->setCode('group');
        $this->save($group);

        $this->findBy(['code' => 'group'])->shouldReturn([$group]);
    }

    function it_returns_an_empty_array_if_criteria_find_nothing()
    {
        $group = new Group();
        $group->setCode('group');
        $this->save($group);

        $this->findBy(['code' => 'group1'])->shouldReturn([]);
    }
}
