<?php

namespace spec\PimEnterprise\Bundle\FilterBundle\Filter\Product;

use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Repository\ProductCategoryRepositoryInterface;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PermissionFilterSpec extends ObjectBehavior
{
    function let(
        FormFactoryInterface $factory,
        FilterUtility $utility,
        CategoryAccessRepository $accessRepository,
        TokenInterface $token,
        UserInterface $user,
        ProductCategoryRepositoryInterface $productRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $this->beConstructedWith($factory, $utility, $tokenStorage, $productRepository, $accessRepository);
    }

    function it_applies_a_filter_on_owned_products_with_granted_categories(
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
        FilterDatasourceAdapterInterface $datasource,
        $accessRepository,
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
