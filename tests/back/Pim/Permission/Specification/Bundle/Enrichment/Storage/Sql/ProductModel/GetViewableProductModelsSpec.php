<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\ProductModel;

use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProductModels as GetViewableProductModelsInterface;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\ProductModel\FetchUserRightsOnProductModel;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\ProductModel\GetViewableProductModels;
use Akeneo\Pim\Permission\Component\Query\ProductModelCategoryAccessQueryInterface;
use PhpSpec\ObjectBehavior;

class GetViewableProductModelsSpec extends ObjectBehavior
{
    function let(FetchUserRightsOnProductModel $fetchUserRightsOnProductModel) {
        $this->beConstructedWith($fetchUserRightsOnProductModel);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GetViewableProductModels::class);
        $this->shouldImplement(GetViewableProductModelsInterface::class);
    }

    function it_returns_non_viewable_product_model_codes(
        ProductModelCategoryAccessQueryInterface $productModelCategoryAccessQuery,
    ) {
        $productModelCategoryAccessQuery->getGrantedProductModelCodes(['uno', 'dos', 'tres'])
            ->willReturn(['un', 'tres']);
        $this->fromProductModelCodes(['uno', 'dos', 'tres'], 42)->shouldReturn(['un', 'tres']);
    }
}
