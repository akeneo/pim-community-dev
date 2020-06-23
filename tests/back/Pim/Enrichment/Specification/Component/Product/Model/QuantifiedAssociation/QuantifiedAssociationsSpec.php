<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociations;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedAssociationsSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough(
            'createWithAssociationsAndMapping',
            [
                [
                    'PACK' => [
                        'products'       => [
                            ['id' => 1, 'quantity' => 1],
                            ['id' => 2, 'quantity' => 2],
                        ],
                        'product_models' => [
                            ['id' => 1, 'quantity' => 1],
                            ['id' => 2, 'quantity' => 2],
                        ],
                    ]
                ],
                $this->anIdMapping(),
                $this->anIdMapping()
            ]
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(QuantifiedAssociations::class);
    }

    public function it_is_normalizable()
    {
        $expectedRawQuantifiedAssociations = [
            'PACK' => [
                'products'       => [
                    ['id' => 1, 'quantity' => 1],
                    ['id' => 2, 'quantity' => 2],
                ],
                'product_models' => [
                    ['id' => 1, 'quantity' => 1],
                    ['id' => 2, 'quantity' => 2],
                ],
            ]
        ];

        $this->beConstructedThrough(
            'createWithAssociationsAndMapping',
            [
                $expectedRawQuantifiedAssociations,
                $this->anIdMapping(),
                $this->anIdMapping()
            ]
        );

        $this->normalizeWithMapping($this->anIdMapping(), $this->anIdMapping())->shouldReturn($expectedRawQuantifiedAssociations);
    }

    public function it_ignores_unknown_products_andProductModels()
    {
        $rawQuantifiedAssociations = [
            'PACK' => [
                'products'       => [
                    ['id' => 1, 'quantity' => 1],
                    ['id' => 2, 'quantity' => 2],
                ],
                'product_models' => [
                    ['id' => 1, 'quantity' => 1],
                    ['id' => 2, 'quantity' => 2],
                ],
            ]
        ];

        $expectedNormalizedAssociations = [
            'PACK' => [
                'products'       => [
                    ['id' => 1, 'quantity' => 1],
                ],
                'product_models' => [
                    ['id' => 1, 'quantity' => 1],
                ],
            ]
        ];

        $this->beConstructedThrough(
            'createWithAssociationsAndMapping',
            [
                $rawQuantifiedAssociations,
                $this->anIncompleteIdMapping(),
                $this->anIncompleteIdMapping()
            ]
        );

        $this->normalizeWithMapping($this->anIncompleteIdMapping(), $this->anIncompleteIdMapping())->shouldReturn($expectedNormalizedAssociations);
    }

    public function it_returns_the_list_of_product_identifiers()
    {
        $expectedRawQuantifiedAssociations = [
            'PACK' => [
                'products'       => [
                    ['id' => 1, 'quantity' => 1],
                    ['id' => 2, 'quantity' => 2],
                ],
                'product_models' => [],
            ]
        ];
        $idMapping = IdMapping::createFromMapping(
            [
                1 => 'entity_1',
                2 => 'entity_2'
            ]
        );

        $this->beConstructedThrough(
            'createWithAssociationsAndMapping',
            [
                $expectedRawQuantifiedAssociations,
                $idMapping,
                $idMapping
            ]
        );

        $this->getQuantifiedAssociationsProductIdentifiers()->shouldReturn(['entity_1', 'entity_2']);
    }

    public function it_returns_the_list_of_product_model_codes()
    {
        $expectedRawQuantifiedAssociations = [
            'PACK' => [
                'products'       => [],
                'product_models' => [
                    ['id' => 1, 'quantity' => 1],
                    ['id' => 2, 'quantity' => 2],
                ],
            ]
        ];
        $idMapping = IdMapping::createFromMapping(
            [
                1 => 'entity_1',
                2 => 'entity_2'
            ]
        );

        $this->beConstructedThrough(
            'createWithAssociationsAndMapping',
            [
                $expectedRawQuantifiedAssociations,
                $idMapping,
                $idMapping
            ]
        );

        $this->getQuantifiedAssociationsProductModelCodes()->shouldReturn(['entity_1', 'entity_2']);
    }

    // Products
    public function it_cannot_be_created_if_the_raw_associations_does_not_have_a_list_of_product_associations()
    {
        $expectedRawQuantifiedAssociations = [
            'PACK' => [
                // No 'products' quantified associations
                'product_models' => [],
            ]
        ];
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during(
                'createWithAssociationsAndMapping',
                [
                    $expectedRawQuantifiedAssociations,
                    $this->anIdMapping(),
                    $this->anIdMapping()
                ]
            );
    }

    public function it_cannot_be_created_if_a_product_raw_quantified_link_does_not_have_an_id()
    {
        $expectedRawQuantifiedAssociations = [
            'PACK' => [
                'products' => [
                    ['quantity' => 1], // no 'id'
                ],
                'product_models' => [],
            ]
        ];
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during(
                'createWithAssociationsAndMapping',
                [
                    $expectedRawQuantifiedAssociations,
                    $this->anIdMapping(),
                    $this->anIdMapping()
                ]
            );
    }

    public function it_cannot_be_created_if_a_product_raw_quantified_link_does_not_have_a_quantity()
    {
        $expectedRawQuantifiedAssociations = [
            'PACK' => [
                'products' => [
                    ['id' => 1], // no 'quantity'
                ],
                'product_models' => [],
            ]
        ];
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during(
                'createWithAssociationsAndMapping',
                [
                    $expectedRawQuantifiedAssociations,
                    $this->anIdMapping(),
                    $this->anIdMapping()
                ]
            );
    }

    // Product models
    public function it_cannot_be_created_if_the_raw_associations_does_not_have_a_list_of_product_models_associations()
    {
        $expectedRawQuantifiedAssociations = [
            'PACK' => [
                'products' => [],
                // No 'product_models' quantified associations
            ]
        ];
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during(
                'createWithAssociationsAndMapping',
                [
                    $expectedRawQuantifiedAssociations,
                    $this->anIdMapping(),
                    $this->anIdMapping()
                ]
            );
    }

    public function it_cannot_be_created_if_a_product_model_raw_quantified_link_does_not_have_an_id()
    {
        $expectedRawQuantifiedAssociations = [
            'PACK' => [
                'products' => [],
                'product_models' => [
                    ['quantity' => 1], // no 'id'
                ],
            ]
        ];
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during(
                'createWithAssociationsAndMapping',
                [
                    $expectedRawQuantifiedAssociations,
                    $this->anIdMapping(),
                    $this->anIdMapping()
                ]
            );
    }

    public function it_cannot_be_created_if_a_product_model_raw_quantified_link_does_not_have_a_quantity()
    {
        $expectedRawQuantifiedAssociations = [
            'PACK' => [
                'products' => [],
                'product_models' => [
                    ['id' => 1], // no 'quantity'
                ],
            ]
        ];
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during(
                'createWithAssociationsAndMapping',
                [
                    $expectedRawQuantifiedAssociations,
                    $this->anIdMapping(),
                    $this->anIdMapping()
                ]
            );
    }

    public function it_merge_quantified_associations_and_overwrite_quantities_from_duplicated_identifiers(
        QuantifiedAssociations $quantifiedAssociationsToMerge
    ) {
        $this->beConstructedThrough(
            'createFromNormalized',
            [
                [
                    'PACK' => [
                        'products' => [
                            ['identifier' => 'A', 'quantity' => 2],
                            ['identifier' => 'B', 'quantity' => 3],
                            ['identifier' => 'C', 'quantity' => 4],
                        ],
                    ],
                ]
            ]
        );

        $quantifiedAssociationsToMerge->normalize()->willReturn([
            'PACK' => [
                'products' => [
                    ['identifier' => 'B', 'quantity' => 3],
                    ['identifier' => 'C', 'quantity' => 6],
                    ['identifier' => 'D', 'quantity' => 1],
                ],
            ],
        ]);

        $this->merge($quantifiedAssociationsToMerge);

        $this->normalize()->shouldReturn([
            'PACK' => [
                'products' => [
                    ['identifier' => 'A', 'quantity' => 2],
                    ['identifier' => 'B', 'quantity' => 3],
                    ['identifier' => 'C', 'quantity' => 6],
                    ['identifier' => 'D', 'quantity' => 1],
                ],
                'product_models' => []
            ],
        ]);
    }

    private function anIdMapping(): IdMapping
    {
        return IdMapping::createFromMapping(
            [
                1 => 'entity_1',
                2 => 'entity_2'
            ]
        );
    }

    private function anIncompleteIdMapping(): IdMapping
    {
        return IdMapping::createFromMapping(
            [
                1 => 'entity_1'
            ]
        );
    }
}
