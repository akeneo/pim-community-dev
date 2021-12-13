<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Manager;

use Akeneo\Pim\Permission\Bundle\Entity\AttributeGroupAccess;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\UserManagement\Component\Model\Group;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\AttributeGroupAccessRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Prophecy\Argument;

class AttributeGroupAccessManagerSpec extends ObjectBehavior
{
    function let(
        AttributeGroupAccessRepository $repository,
        BulkSaverInterface $saver,
        BulkRemoverInterface $remover
    ) {
        $this->beConstructedWith(
            $repository,
            $saver,
            AttributeGroupAccess::class,
            $remover
        );
    }

    function it_provides_user_groups_that_have_access_to_an_attribute_group(AttributeGroupInterface $group, $repository)
    {
        $repository->getGrantedUserGroups($group, Attributes::VIEW_ATTRIBUTES)->willReturn(['foo', 'baz']);
        $repository->getGrantedUserGroups($group, Attributes::EDIT_ATTRIBUTES)->willReturn(['baz']);

        $this->getViewUserGroups($group)->shouldReturn(['foo', 'baz']);
        $this->getEditUserGroups($group)->shouldReturn(['baz']);
    }

    function it_grants_access_on_an_attribute_group_for_the_provided_user_groups(
        AttributeGroupInterface $group,
        Group $manager,
        Group $redactor,
        $repository,
        $saver
    ) {
        $repository->findOneBy(Argument::any())->willReturn(array());
        $repository->revokeAccess($group, [$redactor, $manager])->shouldBeCalled();
        $saver->saveAll(Argument::size(2))->shouldBeCalled();

        $this->setAccess($group, [$manager, $redactor], [$redactor]);
    }
}
