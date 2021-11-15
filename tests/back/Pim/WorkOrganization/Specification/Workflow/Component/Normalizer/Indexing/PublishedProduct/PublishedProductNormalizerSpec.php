<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\PublishedProduct;

use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompleteness;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompletenessCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\PublishedProduct\PublishedProductNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\GetPublishedProductCompletenesses;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class PublishedProductNormalizerSpec extends ObjectBehavior
{
    function let(GetPublishedProductCompletenesses $getPublishedProductCompletenesses, NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($getPublishedProductCompletenesses);
        $this->setNormalizer($normalizer);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_is_a_published_product_normalizer()
    {
        $this->shouldHaveType(PublishedProductNormalizer::class);
    }

    function it_only_normalizes_a_published_product_for_indexing_format()
    {
        $this->supportsNormalization(new PublishedProduct(), PublishedProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
             ->shouldReturn(true);
        $this->supportsNormalization(new \stdClass(), PublishedProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
             ->shouldReturn(false);
        $this->supportsNormalization(new PublishedProduct(), 'another_format')
             ->shouldReturn(false);
    }

    function it_normalizes_a_published_product(
        GetPublishedProductCompletenesses $getPublishedProductCompletenesses,
        NormalizerInterface $normalizer,
        PublishedProductInterface $publishedProduct
    ) {
        $publishedProduct->getId()->willReturn(42);
        $publishedProduct->getIdentifier()->willReturn('my_identifier');
        $dateTime = new \DateTime('2019-07-16');
        $publishedProduct->getCreated()->willReturn($dateTime);
        $publishedProduct->getUpdated()->willReturn($dateTime);
        $normalizer->normalize($dateTime, PublishedProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
                   ->willReturn('2019-07-16T14:25:04+00:00');
        $family = new Family();
        $attributeAsLabel = new Attribute();
        $attributeAsLabel->setCode('name');
        $family->setAttributeAsLabel($attributeAsLabel);
        $normalizer->normalize($family, PublishedProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
                   ->willReturn(
                       [
                           'code' => 'clothing',
                           'labels' => [
                               'en_US' => 'Clothing',
                               'fr_FR' => 'Vêtements',
                           ],
                       ]
                   );
        $publishedProduct->getFamily()->willReturn($family);
        $publishedProduct->isEnabled()->willReturn(true);
        $publishedProduct->getCategoryCodes()->willReturn(['men', 'summer']);
        $publishedProduct->getGroupCodes()->willReturn(['promotions']);

        $completenessCollection = new PublishedProductCompletenessCollection(
            42,
            [
                new PublishedProductCompleteness('ecommerce', 'en_US', 5, ['description']),
                new PublishedProductCompleteness('ecommerce', 'fr_FR', 7, []),
            ]
        );
        $getPublishedProductCompletenesses->fromPublishedProductId(42)->willReturn($completenessCollection);
        $normalizer->normalize(
            $completenessCollection,
            PublishedProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX,
            ['is_published_product' => true]
        )->willReturn(
            [
                'ecommerce' => [
                    'en_US' => 80,
                    'fr_FR' => 100,
                ],
            ]
        );

        $values = new WriteValueCollection(
            [
                ScalarValue::localizableValue('name', 'Great pants', 'en_US'),
                ScalarValue::localizableValue('name', 'Super pantalon', 'fr_FR'),
            ]
        );
        $publishedProduct->getValues()->willReturn($values);
        $normalizer->normalize($values, PublishedProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX, ['is_published_product' => true])->willReturn(
            [
                'name-text' => [
                    '<all_channels>' => [
                        'en_US' => 'Great pants',
                        'fr_FR' => 'Super pantalon',
                    ],
                ],
            ]
        );

        $this->normalize($publishedProduct, PublishedProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX, [])->shouldReturn(
            [
                'id' => '42',
                'identifier' => 'my_identifier',
                'created' => '2019-07-16T14:25:04+00:00',
                'updated' => '2019-07-16T14:25:04+00:00',
                'family' => [
                    'code' => 'clothing',
                    'labels' => [
                        'en_US' => 'Clothing',
                        'fr_FR' => 'Vêtements',
                    ],
                ],
                'enabled' => true,
                'categories' => ['men', 'summer'],
                'groups' => ['promotions'],
                'in_group' => [
                    'promotions' => true,
                ],
                'completeness' => [
                    'ecommerce' => [
                        'en_US' => 80,
                        'fr_FR' => 100,
                    ],
                ],
                'values' => [
                    'name-text' => [
                        '<all_channels>' => [
                            'en_US' => 'Great pants',
                            'fr_FR' => 'Super pantalon',
                        ],
                    ],
                ],
                'label' => [
                    '<all_channels>' => [
                        'en_US' => 'Great pants',
                        'fr_FR' => 'Super pantalon',
                    ],
                ],
                'ancestors' => [
                    'ids' => [],
                    'codes' => [],
                ],
            ]
        );
    }
}
