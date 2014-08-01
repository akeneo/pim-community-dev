<?php

namespace spec\PimEnterprise\Bundle\FilterBundle\Filter\Product;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormFactoryInterface;
use Prophecy\Argument;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Oro\Bundle\UserBundle\Entity\User;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Doctrine\ORM\QueryBuilder;

class PermissionFilterSpec extends ObjectBehavior
{
    function let(
        FormFactoryInterface $factory,
        FilterUtility $utility,
        SecurityContextInterface $securityContext,
        CategoryAccessRepository $accessRepository,
        TokenInterface $token,
        User $user,
        ProductCategoryRepositoryInterface $productRepository,
        QueryBuilder $qb
    ) {
        $securityContext->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $this->beConstructedWith($factory, $utility, $securityContext, $productRepository, $accessRepository);
    }

    function it_applies_a_filter_on_owned_products_with_granted_categories(
        $utility,
        FilterDatasourceAdapterInterface $datasource,
        $accessRepository,
        $productRepository,
        $user,
        $qb
    ) {
        $accessRepository->getGrantedCategoryIds($user, Attributes::OWN_PRODUCTS)->willReturn([42, 19]);
        $datasource->getQueryBuilder()->willReturn($qb);
        $productRepository->applyFilterByCategoryIdsOrUnclassified($qb, [42, 19])->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => 3,
                'type' => null
            ]
        )->shouldReturn(true);
    }

    function it_applies_a_filter_on_owned_products_without_granted_categories(
        $utility,
        FilterDatasourceAdapterInterface $datasource,
        $accessRepository,
        $manager,
        $productRepository,
        $user,
        $qb
    ) {
        $accessRepository->getGrantedCategoryIds($user, Attributes::OWN_PRODUCTS)->willReturn([]);
        $datasource->getQueryBuilder()->willReturn($qb);
        $productRepository->applyFilterByUnclassified($qb)->shouldBeCalled();

        $this->apply(
            $datasource,
            [
                'value' => 3,
                'type' => null
            ]
        )->shouldReturn(true);
    }
}
