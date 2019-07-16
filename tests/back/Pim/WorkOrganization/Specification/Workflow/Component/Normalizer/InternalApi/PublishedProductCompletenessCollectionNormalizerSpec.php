<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\InternalApi;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompleteness;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompletenessCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\InternalApi\PublishedProductCompletenessNormalizer;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PublishedProductCompletenessCollectionNormalizerSpec extends ObjectBehavior
{
    function let(
        ObjectRepository $channelRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith(
            $channelRepository,
            $localeRepository,
            $attributeRepository,
            new PublishedProductCompletenessNormalizer()
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

    function it_returns_an_empty_array_for_an_empty_collection()
    {
        $this->normalize(new PublishedProductCompletenessCollection(1, []))->shouldReturn([]);
    }

    function it_normalizes_a_published_product_completeness_collection(
        ObjectRepository $channelRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        NormalizerInterface $standardNormalizer
    ) {
        $frFR = new Locale();
        $frFR->setCode('fr_FR');
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($frFR);
        $enUS = new Locale();
        $enUS->setCode('en_US');
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enUS);

        $ecommerce = new Channel();
        $ecommerce->setCode('ecommerce');
        $ecommerce->setLocale('en_US');
        $ecommerce->setLabel('Ecommerce');
        $ecommerce->setLocale('fr_FR');
        $ecommerce->setLabel('Ecommerce');
        $mobile = new Channel();
        $mobile->setCode('mobile');
        $mobile->setLocale('en_US');
        $mobile->setLabel('Mobile');
        $mobile->setLocale('fr_FR');
        $mobile->setLabel('Mobile');
        $channelRepository->findBy(['code' => ['ecommerce', 'mobile']])->willReturn([$ecommerce, $mobile]);

        $weight = new Attribute();
        $weight->setCode('weight');
        $weight->setLocale('en_US');
        $weight->setLabel('Weight');
        $weight->setLocale('fr_FR');
        $weight->setLabel('Poids');
        $attributeRepository->findOneByIdentifier('weight')->willReturn($weight);
        $description = new Attribute();
        $description->setCode('description');
        $description->setLocale('en_US');
        $description->setLabel('Description');
        $description->setLocale('fr_FR');
        $description->setLabel('Description');
        $attributeRepository->findOneByIdentifier('description')->willReturn($description);
        $picture = new Attribute();
        $picture->setCode('picture');
        $picture->setLocale('en_US');
        $picture->setLabel('Picture');
        $picture->setLocale('fr_FR');
        $picture->setLabel('Image');
        $attributeRepository->findOneByIdentifier('picture')->willReturn($picture);

        $collection = new PublishedProductCompletenessCollection(
            42,
            [
                new PublishedProductCompleteness('ecommerce', 'en_US', 5, []),
                new PublishedProductCompleteness('ecommerce', 'fr_FR', 5, ['description', 'weight']),
                new PublishedProductCompleteness('mobile', 'en_US', 8, ['description', 'picture']),
                new PublishedProductCompleteness('mobile', 'fr_FR', 8, ['description']),
            ]
        );
        $this->normalize($collection, 'internal_api')->shouldReturn(
            [
                [
                    'channel' => 'ecommerce',
                    'labels' => [
                        'en_US' => 'Ecommerce',
                        'fr_FR' => 'Ecommerce',
                    ],
                    'locales' => [
                        'en_US' => [
                            'completeness' => [
                                'required' => 5,
                                'missing' => 0,
                                'ratio' => 100,
                                'locale' => 'en_US',
                                'channel' => 'ecommerce',
                            ],
                            'missing' => [],
                            'label' => 'English (United States)',
                        ],
                        'fr_FR' => [
                            'completeness' => [
                                'required' => 5,
                                'missing' => 2,
                                'ratio' => 60,
                                'locale' => 'fr_FR',
                                'channel' => 'ecommerce',
                            ],
                            'missing' => [
                                [
                                    'code' => 'description',
                                    'labels' => [
                                        'en_US' => 'Description',
                                        'fr_FR' => 'Description',
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
                        'en_US' => 'Mobile',
                        'fr_FR' => 'Mobile',
                    ],
                    'locales' => [
                        'en_US' => [
                            'completeness' => [
                                'required' => 8,
                                'missing' => 2,
                                'ratio' => 75,
                                'locale' => 'en_US',
                                'channel' => 'mobile',
                            ],
                            'missing' => [
                                [
                                    'code' => 'description',
                                    'labels' => [
                                        'en_US' => 'Description',
                                        'fr_FR' => 'Description',
                                    ],
                                ],
                                [
                                    'code' => 'picture',
                                    'labels' => [
                                        'en_US' => 'Picture',
                                        'fr_FR' => 'Image',
                                    ],
                                ],
                            ],
                            'label' => 'English (United States)',
                        ],
                        'fr_FR' => [
                            'completeness' => [
                                'required' => 8,
                                'missing' => 1,
                                'ratio' => 87,
                                'locale' => 'fr_FR',
                                'channel' => 'mobile',
                            ],
                            'missing' => [
                                [
                                    'code' => 'description',
                                    'labels' => [
                                        'en_US' => 'Description',
                                        'fr_FR' => 'Description',
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
