<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProducts as GetViewableProductsInterface;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Product\FetchUserRightsOnProduct;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Product\GetViewableProducts;
use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProduct;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GetViewableProductsSpec extends ObjectBehavior
{
    function let(
        FetchUserRightsOnProduct $fetchUserRightsOnProduct,
    ) {
        $this->beConstructedWith($fetchUserRightsOnProduct);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GetViewableProducts::class);
        $this->shouldImplement(GetViewableProductsInterface::class);
    }

    function it_returns_viewable_products(
        FetchUserRightsOnProduct $fetchUserRightsOnProduct,
    ) {
        $userRightsOnProduct = new UserRightsOnProduct('uno', 42, 1, 0, 1, 1);
        $userRightsOnProduct2 = new UserRightsOnProduct('dos', 42, 1, 0, 1, 1);
        $userRightsOnProduct3 = new UserRightsOnProduct('tres', 42, 0, 0, 0, 1);

        $fetchUserRightsOnProduct->fetchByIdentifiers(['uno', 'dos', 'tres'], 42)->willReturn(
            [$userRightsOnProduct, $userRightsOnProduct2, $userRightsOnProduct3]
        );
        $this->fromProductIdentifiers(['uno', 'dos', 'tres'], 42)->shouldReturn(['uno', 'dos']);
    }

    function it_does_nothing_if_product_identifiers_is_empty(
        FetchUserRightsOnProduct $fetchUserRightsOnProduct
    ) {
        $fetchUserRightsOnProduct->fetchByIdentifiers(Argument::cetera())->shouldNotBeCalled();
        $this->fromProductIdentifiers([], 42)->shouldReturn([]);
    }
}
