<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Manager;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use Prophecy\Argument;

class AttributeGroupAccessManagerSpec extends ObjectBehavior
{
    function let(
        AttributeGroupAccessRepository $repository,
        BulkSaverInterface $saver
    ) {
        $this->beConstructedWith(
            $repository,
            $saver,
            'PimEnterprise\Bundle\SecurityBundle\Entity\AttributeGroupAccess'
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
