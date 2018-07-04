<?php

namespace spec\Akeneo\Test\Acceptance\Catalog;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Catalog\InMemoryGroupTypeRepository;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\GroupType;
use Akeneo\Pim\Structure\Component\Repository\GroupTypeRepositoryInterface;
use Prophecy\Argument;

class InMemoryGroupTypeRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryGroupTypeRepository::class);
    }

    function it_is_a_group_type_repository()
    {
        $this->shouldImplement(GroupTypeRepositoryInterface::class);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement(SaverInterface::class);
    }

    function it_saves_a_group_type()
    {
        $this->save(new GroupType())->shouldReturn(null);
    }

    function it_only_saves_group_types()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('save', ['wrong_object']);
    }

    function it_finds_a_group_type_by_its_identifier()
    {
        $group = new GroupType();
        $group->setCode('group_type_code');
        $this->save($group);
        $this->findOneByIdentifier('group_type_code')->shouldReturn($group);
    }

    function it_returns_null_if_the_group_type_does_not_exist()
    {
        $this->findOneByIdentifier('group_type_code')->shouldReturn(null);
    }

    function it_has_identifier_properties()
    {
        $this->getIdentifierProperties()->shouldReturn(['code']);
    }

    function it_finds_attribute_group_by_criteria()
    {
        $groupType = new GroupType();
        $groupType->setCode('group_type');
        $this->save($groupType);

        $this->findBy(['code' => 'group_type'])->shouldReturn([$groupType]);
    }

    function it_returns_an_empty_array_if_criteria_find_nothing()
    {
        $groupType = new GroupType();
        $groupType->setCode('group_type');
        $this->save($groupType);

        $this->findBy(['code' => 'group_type1'])->shouldReturn([]);
    }
}
