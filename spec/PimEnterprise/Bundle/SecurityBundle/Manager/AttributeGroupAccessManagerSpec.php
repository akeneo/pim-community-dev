<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Group;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

class AttributeGroupAccessManagerSpec extends ObjectBehavior
{
    function let(
        SmartManagerRegistry $registry,
        ObjectManager $objectManager,
        AttributeGroupAccessRepository $repository
    ) {
        $registry->getManagerForClass(Argument::any())->willReturn($objectManager);
        $registry->getRepository(Argument::any())->willReturn($repository);

        $this->beConstructedWith($registry, 'PimEnterprise\Bundle\SecurityBundle\Entity\AttributeGroupAccess');
    }

    function it_provides_user_groups_that_have_access_to_an_attribute_group(AttributeGroup $group, $repository)
    {
        $repository->getGrantedUserGroups($group, Attributes::VIEW_ATTRIBUTES)->willReturn(['foo', 'baz']);
        $repository->getGrantedUserGroups($group, Attributes::EDIT_ATTRIBUTES)->willReturn(['baz']);

        $this->getViewUserGroups($group)->shouldReturn(['foo', 'baz']);
        $this->getEditUserGroups($group)->shouldReturn(['baz']);
    }

    function it_grants_access_on_an_attribute_group_for_the_provided_user_groups(
        AttributeGroup $group,
        $repository,
        $objectManager,
        Group $manager,
        Group $redactor
    ) {
        $repository->findOneBy(Argument::any())->willReturn(array());
        $repository->revokeAccess($group, [$redactor, $manager])->shouldBeCalled();

        $objectManager
            ->persist(Argument::type('PimEnterprise\Bundle\SecurityBundle\Entity\AttributeGroupAccess'))
            ->shouldBeCalledTimes(2);
        $objectManager->flush()->shouldBeCalled();

        $this->setAccess($group, [$manager, $redactor], [$redactor]);
    }
}
