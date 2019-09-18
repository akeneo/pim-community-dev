<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\MissingRequiredAttributesNormalizer;
use PhpSpec\ObjectBehavior;

class MissingRequiredAttributesNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MissingRequiredAttributesNormalizer::class);
    }

    function it_normalizes_a_completeness_collection()
    {
        $completenessCollection = new ProductCompletenessWithMissingAttributeCodesCollection(
            42,
            [
                new ProductCompletenessWithMissingAttributeCodes('ecommerce', 'en_US', 5, ['name', 'description']),
                new ProductCompletenessWithMissingAttributeCodes(
                    'ecommerce',
                    'fr_FR',
                    5,
                    ['name', 'description', 'weight']
                ),
                new ProductCompletenessWithMissingAttributeCodes('tablet', 'de_DE', 1, []),
                new ProductCompletenessWithMissingAttributeCodes('mobile', 'en_US', 10, ['price', 'name']),
                new ProductCompletenessWithMissingAttributeCodes('tablet', 'en_US', 2, ['description']),
            ]
        );

        $this->normalize($completenessCollection)->shouldReturn(
            [
                [
                    'channel' => 'ecommerce',
                    'locales' => [
                        'en_US' => [
                            'missing' => [
                                ['code' => 'name'],
                                ['code' => 'description'],
                            ],
                        ],
                        'fr_FR' => [
                            'missing' => [
                                ['code' => 'name'],
                                ['code' => 'description'],
                                ['code' => 'weight'],
                            ],
                        ],
                    ],
                ],
                [
                    'channel' => 'tablet',
                    'locales' => [
                        'de_DE' => [
                            'missing' => [],
                        ],
                        'en_US' => [
                            'missing' => [
                                ['code' => 'description'],
                            ],
                        ],
                    ],
                ],
                [
                    'channel' => 'mobile',
                    'locales' => [
                        'en_US' => [
                            'missing' => [
                                ['code' => 'price'],
                                ['code' => 'name'],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}
