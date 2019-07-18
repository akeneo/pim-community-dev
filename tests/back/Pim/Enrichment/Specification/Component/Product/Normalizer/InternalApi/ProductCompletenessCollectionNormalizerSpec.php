<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductCompletenessCollectionNormalizer;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductCompletenessCollectionNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        ChannelRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $this->beConstructedWith(
            $normalizer,
            $channelRepository,
            $attributeRepository,
            $localeRepository
        );
    }

    function it_is_a_product_completeness_collection_normalizer()
    {
        $this->shouldHaveType(ProductCompletenessCollectionNormalizer::class);
    }

    function it_normalizes_completenesses_and_indexes_them(
        NormalizerInterface $normalizer,
        ChannelRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $completenessCollection = new ProductCompletenessCollection(
            42,
            [
                new ProductCompleteness('mobile', 'en_US', 2, ['name']),
                new ProductCompleteness('mobile', 'fr_FR', 2, ['name']),
                new ProductCompleteness('print', 'en_US', 2, ['name']),
                new ProductCompleteness('print', 'fr_FR', 2, ['name']),
                new ProductCompleteness('1234567890', 'en_US', 2, ['name']),
                new ProductCompleteness('1234567890', 'fr_FR', 2, ['name']),
            ]
        );

        list($mobile, $print, $numeric) = [
            $this->createChannel('mobile', ['en_US' => 'mobile', 'fr_FR' => 'mobile']),
            $this->createChannel('print', ['en_US' => 'print', 'fr_FR' => 'impression']),
            $this->createChannel('1234567890', ['en_US' => '1234567890', 'fr_FR' => '1234567890']),
        ];
        $channelRepository->findBy(['code' => ['mobile', 'print', '1234567890']])->willReturn([
            $mobile,
            $print,
            $numeric
        ]);

        $localeRepository->findOneByIdentifier('en_US')->willReturn((new Locale())->setCode('en_US'));
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn((new Locale())->setCode('fr_FR'));

        $name = $this->createAttribute('name', ['en_US' => 'Name', 'fr_FR' => 'Nom']);
        $attributeRepository->findOneByIdentifier('name')->willReturn($name);

        $normalizer->normalize(
            Argument::type(ProductCompleteness::class),
            'internal_api',
            ['a_context_key' => 'context_value']
        )->willReturn([])->shouldBeCalledTimes(6);

        $this
            ->normalize($completenessCollection, 'internal_api', ['a_context_key' => 'context_value'])
            ->shouldReturn(
                [
                    [
                        'channel' => 'mobile',
                        'labels' => [
                            'en_US' => 'mobile',
                            'fr_FR' => 'mobile',
                        ],
                        'stats' => [
                            'total' => 2,
                            'complete' => 0,
                            'average' => 50,
                        ],
                        'locales' => [
                            'en_US' => [
                                'completeness' => [],
                                'missing' => [
                                    [
                                        'code' => 'name',
                                        'labels' => [
                                            'en_US' => 'Name',
                                            'fr_FR' => 'Nom',
                                        ],
                                    ],
                                ],
                                'label' => 'English (United States)',
                            ],
                            'fr_FR' => [
                                'completeness' => [],
                                'missing' => [
                                    [
                                        'code' => 'name',
                                        'labels' => [
                                            'en_US' => 'Name',
                                            'fr_FR' => 'Nom',
                                        ],
                                    ],
                                ],
                                'label' => 'French (France)',
                            ],
                        ],
                    ],
                    [
                        'channel' => 'print',
                        'labels' => [
                            'en_US' => 'print',
                            'fr_FR' => 'impression',
                        ],
                        'stats' => [
                            'total' => 2,
                            'complete' => 0,
                            'average' => 50,
                        ],
                        'locales' => [
                            'en_US' => [
                                'completeness' => [],
                                'missing' => [
                                    [
                                        'code' => 'name',
                                        'labels' => [
                                            'en_US' => 'Name',
                                            'fr_FR' => 'Nom',
                                        ],
                                    ],
                                ],
                                'label' => 'English (United States)',
                            ],
                            'fr_FR' => [
                                'completeness' => [],
                                'missing' => [
                                    [
                                        'code' => 'name',
                                        'labels' => [
                                            'en_US' => 'Name',
                                            'fr_FR' => 'Nom',
                                        ],
                                    ],
                                ],
                                'label' => 'French (France)',
                            ],
                        ],
                    ],
                    [
                        'channel' => '1234567890',
                        'labels' => [
                            'en_US' => '1234567890',
                            'fr_FR' => '1234567890',
                        ],
                        'stats' => [
                            'total' => 2,
                            'complete' => 0,
                            'average' => 50,
                        ],
                        'locales' => [
                            'en_US' => [
                                'completeness' => [],
                                'missing' => [
                                    [
                                        'code' => 'name',
                                        'labels' => [
                                            'en_US' => 'Name',
                                            'fr_FR' => 'Nom',
                                        ],
                                    ],
                                ],
                                'label' => 'English (United States)',
                            ],
                            'fr_FR' => [
                                'completeness' => [],
                                'missing' => [
                                    [
                                        'code' => 'name',
                                        'labels' => [
                                            'en_US' => 'Name',
                                            'fr_FR' => 'Nom',
                                        ],
                                    ],
                                ],
                                'label' => 'French (France)',
                            ],
                        ],
                    ],
                ]
            );
    }

    private function createChannel(string $code, array $translations): ChannelInterface
    {
        $channel = new Channel();
        $channel->setCode($code);
        foreach ($translations as $localeCode => $label) {
            $channel->setLocale($localeCode);
            $channel->setLabel($label);
        }

        return $channel;
    }

    private function createAttribute(string $code, array $translations): AttributeInterface
    {
        $attribute = new Attribute();
        $attribute->setCode($code);
        foreach ($translations as $localeCode => $label) {
            $attribute->setLocale($localeCode);
            $attribute->setLabel($label);
        }

        return $attribute;
    }
}
