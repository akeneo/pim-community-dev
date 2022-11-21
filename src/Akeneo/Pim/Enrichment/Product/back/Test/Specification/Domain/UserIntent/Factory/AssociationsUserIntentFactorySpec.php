<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProducts;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\AssociationsUserIntentFactory;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class AssociationsUserIntentFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AssociationsUserIntentFactory::class);
    }

    function it_returns_associations_user_intent()
    {
        $this->create('associations', [
            'PACK' => [
                'products' => ['identifier1', 'identifier2'],
                'product_models' => ['code1', 'code2'],
                'groups' => ['code1', 'code2'],
            ],
            'X_SELL' => [
                'products' => [],
                'product_models' => ['code1', 'code2'],
                'groups' => [],
            ],
            11 => [
                'products' => ['identifier1'],
                'product_models' => [],
                'groups' => [],
            ]
        ])->shouldBeLike([
            new ReplaceAssociatedProducts('PACK', ['identifier1', 'identifier2']),
            new ReplaceAssociatedProductModels('PACK', ['code1', 'code2']),
            new ReplaceAssociatedGroups('PACK', ['code1', 'code2']),
            new ReplaceAssociatedProducts('X_SELL', []),
            new ReplaceAssociatedProductModels('X_SELL', ['code1', 'code2']),
            new ReplaceAssociatedGroups('X_SELL', []),
            new ReplaceAssociatedProducts('11', ['identifier1']),
            new ReplaceAssociatedProductModels('11', []),
            new ReplaceAssociatedGroups('11', []),
        ]);
    }

    function it_throws_an_exception_if_data_is_invalid()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', ['associations', 'association']);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', ['associations', null]);
    }
}
