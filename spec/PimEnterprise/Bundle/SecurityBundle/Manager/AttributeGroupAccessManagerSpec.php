<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry;
use Prophecy\Argument;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Voter\AttributeGroupVoter;

class AttributeGroupAccessManagerSpec extends ObjectBehavior
{
    function let(SmartManagerRegistry $registry, ObjectManager $objectManager, AttributeGroupAccessRepository $repository)
    {
        $registry->getManagerForClass(Argument::any())->willReturn($objectManager);
        $registry->getRepository(Argument::any())->willReturn($repository);

        $this->beConstructedWith($registry, 'PimEnterprise\Bundle\SecurityBundle\Entity\AttributeGroupAccess');
    }

    function it_provides_roles_that_have_access_to_an_attribute_group(AttributeGroup $group, $repository)
    {
        $repository->getGrantedRoles($group, AttributeGroupVoter::VIEW_ATTRIBUTES)->willReturn(['foo', 'baz']);
        $repository->getGrantedRoles($group, AttributeGroupVoter::EDIT_ATTRIBUTES)->willReturn(['baz']);

        $this->getViewRoles($group)->shouldReturn(['foo', 'baz']);
        $this->getEditRoles($group)->shouldReturn(['baz']);
    }

    function it_grants_access_on_an_attribute_group_for_the_provided_roles(
        AttributeGroup $group,
        $repository,
        $objectManager,
        Role $user,
        Role $admin
    ) {
        $repository->findOneBy(Argument::any())->willReturn(array());
        $repository->revokeAccess($group, [$admin, $user])->shouldBeCalled();

        $objectManager
            ->persist(Argument::type('PimEnterprise\Bundle\SecurityBundle\Entity\AttributeGroupAccess'))
            ->shouldBeCalledTimes(2);
        $objectManager->flush()->shouldBeCalled();

        $this->setAccess($group, [$user, $admin], [$admin]);
    }
}
