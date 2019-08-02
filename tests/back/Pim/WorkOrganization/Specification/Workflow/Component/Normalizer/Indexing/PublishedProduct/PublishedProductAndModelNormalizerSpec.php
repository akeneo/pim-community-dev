<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\PublishedProduct;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompleteness;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompletenessCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\Indexing\PublishedProduct\PublishedProductAndModelNormalizer;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\GetPublishedProductCompletenesses;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PublishedProductAndModelNormalizerSpec extends ObjectBehavior
{
    function let(GetPublishedProductCompletenesses $getPublishedProductCompletenesses, NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($getPublishedProductCompletenesses, []);
        $this->setNormalizer($normalizer);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_is_a_published_product_and_model_normalizer()
    {
        $this->shouldHaveType(PublishedProductAndModelNormalizer::class);
    }

    function it_only_normalizes_a_published_product_for_indexing_product_and_model_format()
    {
        $this->supportsNormalization(
            new PublishedProduct(),
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX
        )->shouldReturn(true);
        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
             ->shouldReturn(false);
        $this->supportsNormalization(new PublishedProduct(), 'another_format')->shouldReturn(false);
    }

    function it_normalizes_a_published_product(
        GetPublishedProductCompletenesses $getPublishedProductCompletenesses,
        NormalizerInterface $normalizer,
        PublishedProductInterface $publishedProduct,
        FamilyInterface $family
    ) {
        $publishedProduct->getId()->willReturn(42);
        $publishedProduct->getIdentifier()->willReturn('my_identifier');
        $dateTime = new \DateTime('2019-07-16');
        $publishedProduct->getCreated()->willReturn($dateTime);
        $publishedProduct->getUpdated()->willReturn($dateTime);
        $normalizer->normalize($dateTime, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->willReturn(
            '2019-07-16T14:25:04+00:00'
        );

        $attributeAsLabel = new Attribute();
        $attributeAsLabel->setCode('name');
        $family->getAttributeAsLabel()->willReturn($attributeAsLabel);
        $family->getAttributeCodes()->willReturn(['sku', 'name', 'description', 'picture']);
        $normalizer->normalize($family, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)->willReturn(
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
            ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX,
            []
        )
                   ->willReturn(
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
        $normalizer->normalize($values, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX, [])
                   ->willReturn(
                       [
                           'name-text' => [
                               '<all_channels>' => [
                                   'en_US' => 'Great pants',
                                   'fr_FR' => 'Super pantalon',
                               ],
                           ],
                       ]
                   );
        $publishedProduct->getRawValues()->willReturn(
            [
                'name' => [
                    '<all_channels>' => [
                        'en_US' => 'Great pants',
                        'fr_FR' => 'Super pantalon',
                    ],
                ],
            ]
        );

        $this->normalize($publishedProduct, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX, [])
             ->shouldReturn(
                 [
                     'id' => 'product_42',
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
                     'categories_of_ancestors' => [],
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
                     'family_variant' => null,
                     'parent' => null,
                     'values' => [
                         'name-text' => [
                             '<all_channels>' => [
                                 'en_US' => 'Great pants',
                                 'fr_FR' => 'Super pantalon',
                             ],
                         ],
                     ],
                     'ancestors' => [],
                     'label' => [
                         '<all_channels>' => [
                             'en_US' => 'Great pants',
                             'fr_FR' => 'Super pantalon',
                         ],
                     ],
                     'document_type' => ProductInterface::class,
                     'attributes_of_ancestors' => [],
                     'attributes_for_this_level' => ['description', 'name', 'picture', 'sku'],
                 ]
             );
    }
}
