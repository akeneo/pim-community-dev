<?php

namespace spec\PimEnterprise\Bundle\FilterBundle\Filter\Product;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormFactoryInterface;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductCategoryManager;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Oro\Bundle\UserBundle\Entity\User;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Doctrine\ORM\QueryBuilder;

class CategoryFilterSpec extends ObjectBehavior
{
    function let(
        FormFactoryInterface $factory,
        FilterUtility $utility,
        ProductCategoryManager $manager,
        SecurityContextInterface $securityContext,
        CategoryAccessRepository $accessRepository,
        TokenInterface $token,
        User $user,
        ProductCategoryRepositoryInterface $productRepository,
        QueryBuilder $qb,
        FilterDatasourceAdapterInterface $datasource
    ) {
        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $manager->getProductCategoryRepository()->willReturn($productRepository);

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
        $productRepository,
        $user,
        $qb
    ) {
        $accessRepository->getGrantedCategoryIds($user, Attributes::VIEW_PRODUCTS)->willReturn([42, 19]);
        $productRepository->applyFilterByCategoryIdsOrUnclassified($qb, [42, 19])->shouldBeCalled();

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
        $productRepository,
        $user,
        $qb
    ) {
        $accessRepository->getGrantedCategoryIds($user, Attributes::VIEW_PRODUCTS)->willReturn([]);
        $productRepository->applyFilterByUnclassified($qb)->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => ['categoryId' => -2, 'treeId' => 1],
                'type' => 1
            ]
        )->shouldReturn(true);
    }
}
