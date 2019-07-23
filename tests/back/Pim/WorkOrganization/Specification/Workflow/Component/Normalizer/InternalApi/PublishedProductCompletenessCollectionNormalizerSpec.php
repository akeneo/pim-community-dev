<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetAttributeLabelsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetChannelLabelsInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompleteness;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompletenessCollection;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PublishedProductCompletenessCollectionNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $standardNormalizer,
        GetChannelLabelsInterface $getChannelLabels,
        GetAttributeLabelsInterface $getAttributeLabels
    ) {
        $this->beConstructedWith(
            $standardNormalizer,
            $getChannelLabels,
            $getAttributeLabels
        );
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_can_only_normalize_a_published_product_completeness_collection_for_internal_api_format()
    {
        $this->supportsNormalization(new \stdClass(), 'internal_api')->shouldReturn(false);
        $this->supportsNormalization(new PublishedProductCompletenessCollection(1, []), 'other_format')
             ->shouldReturn(false);
        $this->supportsNormalization(new PublishedProductCompletenessCollection(1, []), 'internal_api')
             ->shouldReturn(true);
    }

    function it_returns_an_empty_array_for_an_empty_collection(
        GetChannelLabelsInterface $getChannelLabels,
        GetAttributeLabelsInterface $getAttributeLabels,
        NormalizerInterface $standardNormalizer
    ) {
        $completenesses = new PublishedProductCompletenessCollection(1, []);
        $getChannelLabels->forChannelCodes([])->willReturn([]);
        $getAttributeLabels->forAttributeCodes([])->willReturn([]);

        $this->normalize($completenesses)->shouldReturn([]);
    }

    function it_normalizes_a_published_product_completeness_collection(
        GetChannelLabelsInterface $getChannelLabels,
        GetAttributeLabelsInterface $getAttributeLabels,
        NormalizerInterface $standardNormalizer
    ) {
        $getChannelLabels->forChannelCodes(['ecommerce', 'mobile'])->willReturn([
            'ecommerce' => [
                'en_US' => 'Ecommerce',
                'fr_FR' => 'E-commerce'
            ]
        ]);

        $getAttributeLabels->forAttributeCodes(['description', 'weight', 'picture'])->willReturn([
            'weight' => [
                'en_US' => 'Weight',
                'fr_FR' => 'Poids'
            ],
            'description' => [
                'en_US' => 'Description'
            ]
        ]);

        $completenessEcommerceEn = new PublishedProductCompleteness('ecommerce', 'en_US', 5, []);
        $completenessEcommerceFr = new PublishedProductCompleteness('ecommerce', 'fr_FR', 5, ['description', 'weight']);
        $completenessMobileEn = new PublishedProductCompleteness('mobile', 'en_US', 8, ['description', 'picture']);
        $completenessMobileFr = new PublishedProductCompleteness('mobile', 'fr_FR', 8, ['description']);

        $collection = new PublishedProductCompletenessCollection(
            42,
            [$completenessEcommerceEn, $completenessEcommerceFr, $completenessMobileEn, $completenessMobileFr]
        );

        $standardNormalizer->normalize($completenessEcommerceFr, 'internal_api', [])->willReturn(['normalized completeness Ecommerce FR']);
        $standardNormalizer->normalize($completenessEcommerceEn, 'internal_api', [])->willReturn(['normalized completeness Ecommerce EN']);
        $standardNormalizer->normalize($completenessMobileFr, 'internal_api', [])->willReturn(['normalized completeness Mobile FR']);
        $standardNormalizer->normalize($completenessMobileEn, 'internal_api', [])->willReturn(['normalized completeness Mobile EN']);

        $this->normalize($collection, 'internal_api')->shouldReturn(
            [
                [
                    'channel' => 'ecommerce',
                    'labels' => [
                        'en_US' => 'Ecommerce',
                        'fr_FR' => 'E-commerce',
                    ],
                    'locales' => [
                        'en_US' => [
                            'completeness' => ['normalized completeness Ecommerce EN'],
                            'missing' => [],
                            'label' => 'English (United States)',
                        ],
                        'fr_FR' => [
                            'completeness' => ['normalized completeness Ecommerce FR'],
                            'missing' => [
                                [
                                    'code' => 'description',
                                    'labels' => [
                                        'en_US' => 'Description',
                                        'fr_FR' => '[description]',
                                    ],
                                ],
                                [
                                    'code' => 'weight',
                                    'labels' => [
                                        'en_US' => 'Weight',
                                        'fr_FR' => 'Poids',
                                    ],
                                ],
                            ],
                            'label' => 'French (France)',
                        ],
                    ],
                    'stats' => [
                        'total' => 2,
                        'complete' => 1,
                        'average' => 80,
                    ],
                ],
                [
                    'channel' => 'mobile',
                    'labels' => [
                        'en_US' => '[mobile]',
                        'fr_FR' => '[mobile]',
                    ],
                    'locales' => [
                        'en_US' => [
                            'completeness' => ['normalized completeness Mobile EN'],
                            'missing' => [
                                [
                                    'code' => 'description',
                                    'labels' => [
                                        'en_US' => 'Description',
                                        'fr_FR' => '[description]',
                                    ],
                                ],
                                [
                                    'code' => 'picture',
                                    'labels' => [
                                        'en_US' => '[picture]',
                                        'fr_FR' => '[picture]',
                                    ],
                                ],
                            ],
                            'label' => 'English (United States)',
                        ],
                        'fr_FR' => [
                            'completeness' => ['normalized completeness Mobile FR'],
                            'missing' => [
                                [
                                    'code' => 'description',
                                    'labels' => [
                                        'en_US' => 'Description',
                                        'fr_FR' => '[description]',
                                    ],
                                ],
                            ],
                            'label' => 'French (France)',
                        ],
                    ],
                    'stats' => [
                        'total' => 2,
                        'complete' => 0,
                        'average' => 81,
                    ],
                ],
            ]
        );
    }
}
