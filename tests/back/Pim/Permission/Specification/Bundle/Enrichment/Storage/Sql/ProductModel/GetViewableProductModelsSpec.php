<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\ProductModel;

use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProductModels as GetViewableProductModelsInterface;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\ProductModel\FetchUserRightsOnProductModel;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\ProductModel\GetViewableProductModels;
use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProductModel;
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

    function it_returns_viewable_product_model_codes(
        FetchUserRightsOnProductModel $fetchUserRightsOnProductModel,
    ) {
        $userRightsOnProductModel1 = new UserRightsOnProductModel('un', 42, 0, 0, 1, 1);
        new UserRightsOnProductModel('dos', 42, 0, 0, 0, 1);
        $userRightsOnProductModel3 = new UserRightsOnProductModel('tres', 42, 0, 0, 1, 1);

        $fetchUserRightsOnProductModel->fetchByIdentifiers(['uno', 'dos', 'tres'], 42)
            ->willReturn([$userRightsOnProductModel1, $userRightsOnProductModel3]);
        $this->fromProductModelCodes(['uno', 'dos', 'tres'], 42)->shouldReturn(['un', 'tres']);
    }
}
