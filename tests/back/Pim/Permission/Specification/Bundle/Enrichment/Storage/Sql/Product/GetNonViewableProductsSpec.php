<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Product\Domain\Query\GetNonViewableProducts as GetNonViewableProductsInterface;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Product\GetNonViewableProducts;
use Akeneo\Pim\Permission\Component\Query\ProductCategoryAccessQueryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GetNonViewableProductsSpec extends ObjectBehavior
{
    function let(
        ProductCategoryAccessQueryInterface $productCategoryAccessQuery,
        UserRepositoryInterface $userRepository
    ) {
        $this->beConstructedWith($productCategoryAccessQuery, $userRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GetNonViewableProducts::class);
        $this->shouldImplement(GetNonViewableProductsInterface::class);
    }

    function it_returns_non_viewable_products(
        ProductCategoryAccessQueryInterface $productCategoryAccessQuery,
        UserInterface $user,
        UserRepositoryInterface $userRepository
    ) {
        $userRepository->find(42)->willReturn($user);
        $productCategoryAccessQuery->getGrantedProductIdentifiers(['uno', 'dos', 'tres'], $user)->willReturn(['uno', 'tres']);
        $this->fromProductIdentifiers(['uno', 'dos', 'tres'], 42)->shouldReturn(['dos']);
    }

    function it_does_nothing_if_product_identifiers_is_empty(
        ProductCategoryAccessQueryInterface $productCategoryAccessQuery
    ) {
        $productCategoryAccessQuery->getGrantedProductIdentifiers(Argument::cetera())->shouldNotBeCalled();
        $this->fromProductIdentifiers([], 42)->shouldReturn([]);
    }
}
