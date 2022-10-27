<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProducts as GetViewableProductsInterface;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Product\FetchUserRightsOnProduct;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Product\GetViewableProducts;
use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProduct;
use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProductUuid;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

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

    function it_returns_viewable_products_by_uuids(
        FetchUserRightsOnProduct $fetchUserRightsOnProduct,
    ) {
        $firstProductUuid = Uuid::fromString('bfce8d6d-aff9-4521-a346-b13a40a1c9fe');
        $secondProductUuid = Uuid::fromString('456fe593-fd6b-498b-864c-2889ded4995e');
        $thirdProductUuid = Uuid::fromString('e11bba0e-27a6-470f-a602-6a093c49b9d6');
        $userRightsOnProduct = new UserRightsOnProductUuid($firstProductUuid, 42, 1, 0, 1, 1);
        $userRightsOnProduct2 = new UserRightsOnProductUuid($secondProductUuid, 42, 1, 0, 1, 1);
        $userRightsOnProduct3 = new UserRightsOnProductUuid($thirdProductUuid, 42, 0, 0, 0, 1);

        $fetchUserRightsOnProduct
            ->fetchByUuids([$firstProductUuid, $secondProductUuid, $thirdProductUuid], 42)
            ->willReturn([$userRightsOnProduct, $userRightsOnProduct2, $userRightsOnProduct3]);

        $this
            ->fromProductUuids([$firstProductUuid, $secondProductUuid, $thirdProductUuid], 42)
            ->shouldReturn([$firstProductUuid, $secondProductUuid]);
    }

    function it_does_nothing_if_product_identifiers_is_empty(
        FetchUserRightsOnProduct $fetchUserRightsOnProduct
    ) {
        $fetchUserRightsOnProduct->fetchByIdentifiers(Argument::cetera())->shouldNotBeCalled();
        $this->fromProductIdentifiers([], 42)->shouldReturn([]);
    }
}
