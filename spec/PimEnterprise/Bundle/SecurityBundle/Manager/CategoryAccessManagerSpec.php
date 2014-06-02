<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Category;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Voter\CategoryVoter;
use Prophecy\Argument;
use Oro\Bundle\UserBundle\Entity\Role;

class CategoryAccessManagerSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager, CategoryAccessRepository $repository)
    {
        $objectManager->getRepository('PimEnterpriseSecurityBundle:CategoryAccess')->willReturn($repository);

        $this->beConstructedWith($objectManager);
    }

    function it_provides_roles_that_have_access_to_a_category(Category $category, $repository)
    {
        $repository->getGrantedRoles($category, CategoryVoter::VIEW_PRODUCTS)->willReturn(['foo', 'bar']);
        $repository->getGrantedRoles($category, CategoryVoter::EDIT_PRODUCTS)->willReturn(['bar']);

        $this->getViewRoles($category)->shouldReturn(['foo', 'bar']);
        $this->getEditRoles($category)->shouldReturn(['bar']);
    }

    function it_grants_access_on_a_category_for_the_provided_roles(
        Category $category,
        $repository,
        $objectManager,
        Role $user,
        Role $admin
    ) {
        $repository->findOneBy(Argument::any())->willReturn(array());
        $repository->revokeAccess($category, [$admin, $user])->shouldBeCalled();

        $objectManager
            ->persist(Argument::type('PimEnterprise\Bundle\SecurityBundle\Entity\CategoryAccess'))
            ->shouldBeCalledTimes(2);
        $objectManager->flush()->shouldBeCalled();

        $this->setAccess($category, [$user, $admin], [$admin]);
    }
}
