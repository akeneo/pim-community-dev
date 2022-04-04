<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\ProductModel;

use Akeneo\Pim\Enrichment\Product\Domain\Query\GetNonViewableProductModels as GetNonViewableProductModelsInterface;
use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\ProductModel\GetNonViewableProductModels;
use Akeneo\Pim\Permission\Component\Query\ProductModelCategoryAccessQueryInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PhpSpec\ObjectBehavior;

class GetNonViewableProductModelsSpec extends ObjectBehavior
{

    function let(
        ProductModelCategoryAccessQueryInterface $productModelCategoryAccessQuery,
        UserRepositoryInterface $userRepository
    ) {
        $this->beConstructedWith($productModelCategoryAccessQuery, $userRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GetNonViewableProductModels::class);
        $this->shouldImplement(GetNonViewableProductModelsInterface::class);
    }

    function it_returns_non_viewable_product_model_codes(
        ProductModelCategoryAccessQueryInterface $productModelCategoryAccessQuery,
        UserRepositoryInterface $userRepository,
        UserInterface $user
    ) {
        $userRepository->find(42)->willReturn($user);
        $productModelCategoryAccessQuery->getGrantedProductModelCodes(['uno', 'dos', 'tres'])
            ->willReturn(['un', 'tres']);
        $this->fromProductModelCodes(['uno', 'dos', 'tres'], 42)->shouldReturn(['dos']);
    }
}
