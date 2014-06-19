<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Manager;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryOwnershipRepository;
use PimEnterprise\Bundle\SecurityBundle\Entity\CategoryOwnership;

class CategoryOwnershipManagerSpec extends ObjectBehavior
{
    protected $ownershipClass = 'PimEnterprise\Bundle\SecurityBundle\Entity\CategoryOwnership';

    function let(ObjectManager $manager, CategoryOwnershipRepository $repo)
    {
        $manager->getRepository(Argument::type('string'))->willReturn($repo);

        $this->beConstructedWith($manager);
    }

    function it_grants_category_ownership_to_a_role($manager, Role $role, CategoryInterface $category)
    {
        $manager->persist(Argument::type($this->ownershipClass))->shouldBeCalled();

        $this->grantOwnership($role, $category);
    }

    function it_revokes_category_ownership_from_a_role($manager, $repo, Role $role, CategoryInterface $category)
    {
        $repo->findOneBy(['role' => $role, 'category' => $category])->willReturn(new CategoryOwnership());

        $manager->remove(Argument::type($this->ownershipClass))->shouldBeCalled();

        $this->revokeOwnership($role, $category);
    }

    function it_provides_a_list_of_categories_owned_by_a_role(
        $manager,
        $repo,
        Role $role,
        CategoryInterface $foo,
        CategoryInterface $bar,
        CategoryOwnership $first,
        CategoryOwnership $second
    ) {
        $first->getCategory()->willReturn($foo);
        $second->getCategory()->willReturn($bar);
        $repo->findForRole($role, null)->willReturn([$first, $second]);

        $categories = $this->getOwnedCategories($role);

        $categories->shouldHaveType('Doctrine\Common\Collections\ArrayCollection');
        $categories->shouldHaveCount(2);
        $categories->first()->shouldReturn($foo);
        $categories->last()->shouldReturn($bar);
    }
}
