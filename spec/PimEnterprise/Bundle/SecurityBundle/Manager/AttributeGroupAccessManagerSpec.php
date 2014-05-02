<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\AttributeGroupAccessRepository;

class AttributeGroupAccessManagerSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager, AttributeGroupAccessRepository $repository)
    {
        $objectManager->getRepository('PimEnterpriseSecurityBundle:AttributeGroupAccess')->willReturn($repository);

        $this->beConstructedWith($objectManager);
    }

    function it_provides_roles_that_have_access_to_an_attribute_group(AttributeGroup $group, $repository)
    {
        $repository->getGrantedRoles($group, 'VIEW')->willReturn(['foo', 'baz']);
        $repository->getGrantedRoles($group, 'EDIT')->willReturn(['baz']);

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
        $user->getId()->willReturn(1);
        $admin->getId()->willReturn(2);
        $repository->findOneBy(Argument::any())->willReturn(null);

        $objectManager
            ->persist(Argument::type('PimEnterprise\Bundle\SecurityBundle\Entity\AttributeGroupAccess'))
            ->shouldBeCalledTimes(2);
        $repository->revokeAccess($group, [1, 2])->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->setAccess($group, [$user], [$admin]);
    }
}
