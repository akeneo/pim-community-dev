<?php

namespace Specification\Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\Domain\StandardFormat\Validator\QuantifiedAssociationsStructureValidator;
use Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory\QuantifiedAssociationUserIntentFactory;
use PhpSpec\ObjectBehavior;

class QuantifiedAssociationUserIntentFactorySpec extends ObjectBehavior
{
    function let(QuantifiedAssociationsStructureValidator $quantifiedAssociationsStructureValidator)
    {
        $this->beConstructedWith($quantifiedAssociationsStructureValidator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(QuantifiedAssociationUserIntentFactory::class);
    }

    function it_returns_quantified_association_user_intents()
    {
        $this->create('quantified_associations', [
            'QUANTIFIED_ASS' => [
                'products' => [
                    ['uuid' => '4873080d-32a3-42a7-ae5c-1be518e40f3d', 'quantity' => 10],
                    ['uuid' => '5dd9eb8b-261f-4e76-bf1d-f407063f931d', 'quantity' => 20]
                ],
                'product_models' => [
                    ['identifier' => 'code1', 'quantity' => 20],
                    ['identifier' => 'code2', 'quantity' => 10]
                ],
            ],
            '123' => [
                'products' => [
                    ['uuid' => '62071b85-67af-44dd-8db1-9bc1dab393e7', 'quantity' => 2],
                ],
                'product_models' => [
                    ['identifier' => 'bar', 'quantity' => 5],
                ],
            ],
        ])->shouldBeLike([
            new ReplaceAssociatedQuantifiedProducts('QUANTIFIED_ASS', [
                new QuantifiedEntity('4873080d-32a3-42a7-ae5c-1be518e40f3d', 10),
                new QuantifiedEntity('5dd9eb8b-261f-4e76-bf1d-f407063f931d', 20),
            ]),
            new ReplaceAssociatedQuantifiedProductModels('QUANTIFIED_ASS', [
                new QuantifiedEntity('code1', 20),
                new QuantifiedEntity('code2', 10),
            ]),
            new ReplaceAssociatedQuantifiedProducts('123', [
                new QuantifiedEntity('62071b85-67af-44dd-8db1-9bc1dab393e7', 2),
            ]),
            new ReplaceAssociatedQuantifiedProductModels('123', [
                new QuantifiedEntity('bar', 5),
            ]),
        ]);
    }
}
