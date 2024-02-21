<?php

namespace spec\Akeneo\Test\Acceptance\User;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\User\InMemoryGroupRepository;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\Group;
use Prophecy\Argument;

class InMemoryGroupRepositorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(InMemoryGroupRepository::class);
    }

    function it_is_a_identifiable_object_repository()
    {
        $this->shouldImplement(IdentifiableObjectRepositoryInterface::class);
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
        $group->setName('group_name');
        $this->save($group);
        $this->findOneByIdentifier('group_name')->shouldReturn($group);
    }

    function it_returns_null_if_the_group_does_not_exist()
    {
        $this->findOneByIdentifier('group_name')->shouldReturn(null);
    }

    function it_has_identifier_properties()
    {
        $this->getIdentifierProperties()->shouldReturn(['name']);
    }

    function it_returns_all_groups()
    {
        $group1 = new Group();
        $group1->setName('group_name');
        $this->save($group1);
        $group2 = new Group();
        $group2->setName('group_name2');
        $this->save($group2);

        $this->findAll()->shouldReturn(['group_name' => $group1, 'group_name2' => $group2]);
    }
}
