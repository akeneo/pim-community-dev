<?php

namespace spec\PimEnterprise\Bundle\FilterBundle\Filter\Product;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\ProductCategoryManager;
use Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;

class CategoryFilterSpec extends ObjectBehavior
{
    function let(
        FormFactoryInterface $factory,
        ProductFilterUtility $utility,
        ProductCategoryManager $manager,
        SecurityContextInterface $securityContext,
        CategoryAccessRepository $accessRepository,
        TokenInterface $token,
        User $user,
        QueryBuilder $qb,
        FilterDatasourceAdapterInterface $datasource
    ) {
        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getGroups()->willReturn(new ArrayCollection());

        $datasource->getQueryBuilder()->willReturn($qb);

        $this->beConstructedWith($factory, $utility, $manager, $securityContext, $accessRepository);
    }

    function it_extends_the_ce_filter()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\FilterBundle\Filter\Product\CategoryFilter');
    }

    function it_applies_a_filter_on_products_by_all_with_granted_categories(
        $datasource,
        $accessRepository,
        $user,
        $qb,
        $utility
    ) {
        $accessRepository->getGrantedCategoryIds($user, Attributes::VIEW_PRODUCTS)->willReturn([42, 19]);
        $utility->applyFilter($datasource, 'categories.id', 'IN OR UNCLASSIFIED', [42, 19])->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => ['categoryId' => -2, 'treeId' => 1],
                'type' => 1
            ]
        )->shouldReturn(true);
    }

    function it_applies_a_filter_on_products_by_all_without_granted_categories(
        $datasource,
        $accessRepository,
        $user,
        $qb,
        $utility
    ) {
        $accessRepository->getGrantedCategoryIds($user, Attributes::VIEW_PRODUCTS)->willReturn([]);
        $utility->applyFilter($datasource, 'categories.id', 'UNCLASSIFIED', [])->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => ['categoryId' => -2, 'treeId' => 1],
                'type' => 1
            ]
        )->shouldReturn(true);
    }

    function it_applies_a_filter_by_unclassified_products_with_permissions(
        FilterDatasourceAdapterInterface $datasource,
        $utility,
        $manager,
        CategoryRepository $repo,
        CategoryInterface $tree,
        $accessRepository,
        $user,
        $securityContext
    ) {
        $manager->getCategoryRepository()->willReturn($repo);
        $tree->getId()->willReturn(1);
        $repo->find(1)->willReturn($tree);

        $repo->getAllChildrenIds($tree)->willReturn([2, 3, 4]);
        $accessRepository->getGrantedCategoryIds($user, Attributes::VIEW_PRODUCTS)->willReturn([2, 3, 4, 5, 6, 7]);

        $utility->applyFilter($datasource, 'categories.id', 'NOT IN', [2, 3, 4])->shouldBeCalled();
        $utility->applyFilter($datasource, 'categories.id', 'IN OR UNCLASSIFIED', [2, 3, 4, 5, 6, 7])->shouldBeCalled();

        $this->apply($datasource, ['value' => ['categoryId' => -1, 'treeId' => 1]]);
    }

    function it_applies_a_filter_by_in_category_with_permissions(
        FilterDatasourceAdapterInterface $datasource,
        $utility,
        $manager,
        CategoryRepository $repo,
        CategoryInterface $category
    ) {
        $manager->getCategoryRepository()->willReturn($repo);
        $repo->find(42)->willReturn($category);
        $category->getId()->willReturn(42);
        $utility->applyFilter($datasource, 'categories.id', 'IN', [42])->shouldBeCalled();

        $this->apply($datasource, ['value' => ['categoryId' => 42], 'type' => false]);
    }

    function it_applies_a_filter_by_in_category_with_children_with_permissions(
        FilterDatasourceAdapterInterface $datasource,
        $utility,
        $manager,
        CategoryRepository $repo,
        CategoryInterface $category,
        $accessRepository,
        $securityContext
    ) {
        $manager->getCategoryRepository()->willReturn($repo);
        $repo->find(42)->willReturn($category);
        $category->getId()->willReturn(42);
        $repo->find(42)->willReturn($category);
        $repo->getAllChildrenIds($category)->willReturn([2, 3]);

        $securityContext->isGranted(Attributes::VIEW_PRODUCTS, $category)->willReturn(true);
        $accessRepository->getCategoryIdsWithExistingAccess([], [2, 3])->willReturn([2]);

        $utility->applyFilter($datasource, 'categories.id', 'IN', [2, 42])->shouldBeCalled();

        $this->apply($datasource, ['value' => ['categoryId' => 42], 'type' => true]);
    }
}
