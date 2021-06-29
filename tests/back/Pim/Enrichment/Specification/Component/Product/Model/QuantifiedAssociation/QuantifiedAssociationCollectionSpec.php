<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\QuantifiedAssociationCollection;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedAssociationCollectionSpec extends ObjectBehavior
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
                $this->anIdMapping(),
                ['PACK']
            ]
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(QuantifiedAssociationCollection::class);
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
                $this->anIdMapping(),
                ['PACK']
            ]
        );

        $this->normalizeWithMapping($this->anIdMapping(), $this->anIdMapping())->shouldReturn($expectedRawQuantifiedAssociations);
    }

    public function it_ignores_unknown_products_product_models_and_association_types()
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
            ],
            'NON_EXISTENT_ASSOCIATION_TYPE' => [
                'products'       => [
                    ['id' => 1, 'quantity' => 1],
                ],
                'product_models' => [
                    ['id' => 1, 'quantity' => 1],
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
                $this->anIncompleteIdMapping(),
                ['PACK']
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
            ],
            'PRODUCT_SET' => [
                'products' => [
                    ['id' => 1, 'quantity' => 3],
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
                $idMapping,
                ['PACK', 'PRODUCT_SET']
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
            ],
            'PRODUCT_SET' => [
                'products' => [],
                'product_models' => [
                    ['id' => 1, 'quantity' => 3],
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
                $idMapping,
                ['PACK', 'PRODUCT_SET']
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
                    $this->anIdMapping(),
                    ['PACK']
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
                    $this->anIdMapping(),
                    ['PACK']
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
                    $this->anIdMapping(),
                    ['PACK']
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
                    $this->anIdMapping(),
                    ['PACK']
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
                    $this->anIdMapping(),
                    ['PACK']
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
                    $this->anIdMapping(),
                    ['PACK']
                ]
            );
    }

    public function it_filter_by_product_identifiers()
    {
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
                        'product_models' => [
                            ['identifier' => 'A', 'quantity' => 2],
                            ['identifier' => 'B', 'quantity' => 3],
                        ],
                    ],
                    'PRODUCTSET' => [
                        'products' => [
                            ['identifier' => 'B', 'quantity' => 3],
                        ],
                        'product_models' => [],
                    ],
                ],
            ]
        );

        $this->filterProductIdentifiers(['A'])->normalize()->shouldReturn([
            'PACK' => [
                'products' => [
                    ['identifier' => 'A', 'quantity' => 2],
                ],
                'product_models' => [
                    ['identifier' => 'A', 'quantity' => 2],
                    ['identifier' => 'B', 'quantity' => 3],
                ],
            ],
            'PRODUCTSET' => [
                'products' => [],
                'product_models' => [],
            ],
        ]);
    }

    public function it_filter_by_product_model_codes()
    {
        $this->beConstructedThrough(
            'createFromNormalized',
            [
                [
                    'PACK' => [
                        'products' => [
                            ['identifier' => 'A', 'quantity' => 2],
                            ['identifier' => 'B', 'quantity' => 3],
                        ],
                        'product_models' => [
                            ['identifier' => 'A', 'quantity' => 2],
                            ['identifier' => 'B', 'quantity' => 3],
                            ['identifier' => 'C', 'quantity' => 4],
                        ],
                    ],
                    'PRODUCTSET' => [
                        'products' => [
                            ['identifier' => 'B', 'quantity' => 3],
                        ],
                        'product_models' => [
                            ['identifier' => 'B', 'quantity' => 5],
                        ],
                    ],
                ],
            ]
        );

        $this->filterProductModelCodes(['A'])->normalize()->shouldReturn([
            'PACK' => [
                'products' => [
                    ['identifier' => 'A', 'quantity' => 2],
                    ['identifier' => 'B', 'quantity' => 3],
                ],
                'product_models' => [
                    ['identifier' => 'A', 'quantity' => 2],
                ],
            ],
            'PRODUCTSET' => [
                'products' => [
                    ['identifier' => 'B', 'quantity' => 3],
                ],
                'product_models' => [],
            ],
        ]);
    }

    public function it_clear_quantified_associations_already_empty()
    {
        $this->beConstructedThrough('createFromNormalized', [[]]);

        $this->clearQuantifiedAssociations()->normalize()->shouldReturn([]);
    }

    public function it_clear_all_quantified_associations_already_empty()
    {
        $this->beConstructedThrough('createFromNormalized', [
            [
                'PRODUCTSET_A' => [
                    'products' => [
                        ['identifier' => 'AKN_TS1', 'quantity' => 2],
                    ],
                    'product_models' => [
                        ['identifier' => 'MODEL_AKN_TS1', 'quantity' => 2],
                    ],
                ],
                'PRODUCTSET_B' => [
                    'products' => [
                        ['identifier' => 'AKN_TSH2', 'quantity' => 2],
                    ],
                    'product_models' => [],
                ],
            ],
        ]);

        $this->clearQuantifiedAssociations()->normalize()->shouldReturn([
            'PRODUCTSET_A' => [
                'products' => [],
                'product_models' => [],
            ],
            'PRODUCTSET_B' => [
                'products' => [],
                'product_models' => [],
            ],
        ]);
    }

    public function it_override_empty_quantified_associations()
    {
        $this->beConstructedThrough('createFromNormalized', [[]]);
        $this->patchQuantifiedAssociations([
            'PRODUCTSET_A' => [
                'products' => [
                    ['identifier' => 'AKN_TS1', 'quantity' => 2],
                ],
            ]
        ])->normalize()->shouldReturn([
            'PRODUCTSET_A' => [
                'products' => [
                    ['identifier' => 'AKN_TS1', 'quantity' => 2],
                ],
                'product_models' => [],
            ],
        ]);
    }

    public function it_override_existing_quantified_associations()
    {
        $this->beConstructedThrough('createFromNormalized', [
            [
                'PRODUCTSET_A' => [
                    'products' => [
                        ['identifier' => 'AKN_TS1', 'quantity' => 2],
                    ],
                    'product_models' => [
                        ['identifier' => 'MODEL_AKN_TS1', 'quantity' => 2],
                    ],
                ],
                'PRODUCTSET_B' => [
                    'products' => [
                        ['identifier' => 'AKN_TSH2', 'quantity' => 2],
                    ],
                    'product_models' => [],
                ],
            ],
        ]);

        $this->patchQuantifiedAssociations([
            'PRODUCTSET_A' => [
                'products' => [
                    ['identifier' => 'AKN_TS1_ALT', 'quantity' => 200],
                ],
            ],
            'PRODUCTSET_C' => [
                'products' => [
                    ['identifier' => 'AKN_TS1_ALT', 'quantity' => 200],
                ],
            ]
        ])->normalize()->shouldReturn([
            'PRODUCTSET_A' => [
                'products' => [
                    ['identifier' => 'AKN_TS1_ALT', 'quantity' => 200],
                ],
                'product_models' => [
                    ['identifier' => 'MODEL_AKN_TS1', 'quantity' => 2],
                ],
            ],
            'PRODUCTSET_B' => [
                'products' => [
                    ['identifier' => 'AKN_TSH2', 'quantity' => 2],
                ],
                'product_models' => [],
            ],
            'PRODUCTSET_C' => [
                'products' => [
                    ['identifier' => 'AKN_TS1_ALT', 'quantity' => 200],
                ],
                'product_models' => [],
            ]
        ]);
    }

    public function it_merge_quantified_associations_and_overwrite_quantities_from_duplicated_identifiers() {
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

        $quantifiedAssociationsToMerge = QuantifiedAssociationCollection::createFromNormalized([
            'PACK' => [
                'products' => [
                    ['identifier' => 'B', 'quantity' => 3],
                    ['identifier' => 'C', 'quantity' => 6],
                    ['identifier' => 'D', 'quantity' => 1],
                ],
            ],
        ]);

        $this->merge($quantifiedAssociationsToMerge)->shouldBeLike(QuantifiedAssociationCollection::createFromNormalized([
            'PACK' => [
                'products' => [
                    ['identifier' => 'A', 'quantity' => 2],
                    ['identifier' => 'B', 'quantity' => 3],
                    ['identifier' => 'C', 'quantity' => 6],
                    ['identifier' => 'D', 'quantity' => 1],
                ],
                'product_models' => []
            ],
        ]));
    }

    public function it_can_compare_itself_to_another_collection()
    {
        $this->beConstructedThrough(
            'createFromNormalized',
            [
                [
                    'type1' => [
                        'products' => [
                            ['identifier' => 'foo', 'quantity' => 2],
                            ['identifier' => 'bar', 'quantity' => 5],
                        ],
                        'product_models' => [
                            ['identifier' => 'baz', 'quantity' => 3],
                        ],
                    ],
                    'type2' => [
                        'products' => [
                            ['identifier' => 'foo', 'quantity' => 10],
                        ],
                    ],
                ],
            ]
        );

        $identicalCollection = QuantifiedAssociationCollection::createFromNormalized(
            [
                'type2' => [
                    'product_models' => [],
                    'products' => [
                        ['quantity' => 10, 'identifier' => 'foo'],
                    ],
                ],
                'type1' => [
                    'product_models' => [
                        ['identifier' => 'baz', 'quantity' => 3],
                    ],
                    'products' => [
                        ['identifier' => 'bar', 'quantity' => 5],
                        ['identifier' => 'foo', 'quantity' => 2],
                    ],
                ],
            ]
        );
        $this->equals($identicalCollection)->shouldBe(true);

        $differentCollection = $identicalCollection = QuantifiedAssociationCollection::createFromNormalized(
            [
                'type2' => [
                    'product_models' => [],
                    'products' => [
                        ['quantity' => 0, 'identifier' => 'foo'],
                    ],
                ],
                'type1' => [
                    'product_models' => [
                        ['identifier' => 'other_sku', 'quantity' => 1],
                    ],
                    'products' => [
                        ['identifier' => 'foo', 'quantity' => 2],
                    ],
                ],
            ]
        );
        $this->equals($differentCollection)->shouldBe(false);
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
