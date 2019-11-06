<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductModelProjection;
use PhpSpec\ObjectBehavior;

class ElasticsearchProductModelProjectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            1,
            'code',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            'family_code',
            ['family_label_1', 'family_label_2'],
            'family_variant_code',
            ['category_code_1', 'category_code_2'],
            ['category_code_of_ancestors_1', 'category_code_of_ancestors_2'],
            'parent_product_model_code',
            [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
            [
                'ecommerce' => ['de_DE' => 1, 'fr_FR' => 0],
                'mobile' => ['de_DE' => 1, 'fr_FR' => 0],
            ],
            [
                'ecommerce' => ['de_DE' => 1, 'fr_FR' => 0],
                'mobile' => ['de_DE' => 0, 'fr_FR' => 1],
            ],
            2,
            ['<all_channels>' => ['en_US' => 'label']],
            ['ancestor_attribute_code_1', 'ancestor_attribute_code_2'],
            ['attribute_for_this_level1', 'attribute_for_this_level2']
        );
    }

    function it_is_an_indexable_product()
    {
        $this->shouldBeAnInstanceOf(ElasticsearchProductModelProjection::class);
    }

    function it_can_be_converted_in_array()
    {
        $this->toArray()->shouldReturn([
            'id' => 'product_model_1',
            'identifier' => 'code',
            'created' => (new \DateTime('2019-04-23 15:55:50', new \DateTimeZone('UTC')))->format('c'),
            'updated' => (new \DateTime('2019-04-25 15:55:50', new \DateTimeZone('UTC')))->format('c'),
            'family' => [
                'code' => 'family_code',
                'labels' => ['family_label_1', 'family_label_2'],
            ],
            'family_variant' => 'family_variant_code',
            'categories' => ['category_code_1', 'category_code_2'],
            'categories_of_ancestors' => ['category_code_of_ancestors_1', 'category_code_of_ancestors_2'],
            'parent' => 'parent_product_model_code',
            'values' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
            'all_complete' => [
                'ecommerce' => ['de_DE' => 1, 'fr_FR' => 0],
                'mobile' => ['de_DE' => 1, 'fr_FR' => 0],
            ],
            'all_incomplete' => [
                'ecommerce' => ['de_DE' => 1, 'fr_FR' => 0],
                'mobile' => ['de_DE' => 0, 'fr_FR' => 1],
            ],
            'ancestors' => [
                'ids' => ['product_model_2'],
                'codes' => ['parent_product_model_code'],
                'labels' => ['<all_channels>' => ['en_US' => 'label']],
            ],
            'label' => ['<all_channels>' => ['en_US' => 'label']],
            'document_type' => 'Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface',
            'attributes_of_ancestors' => ['ancestor_attribute_code_1', 'ancestor_attribute_code_2'],
            'attributes_for_this_level' => ['attribute_for_this_level1', 'attribute_for_this_level2']
        ]);
    }

    function it_adds_additional_data()
    {
        $this->addAdditionalData(['foo' => 'bar', 'baz' => ['42', '44']])
            ->addAdditionalData(['other_additional_data' => 'some data'])
            ->shouldBeLike(
                new ElasticsearchProductModelProjection(
                    1,
                    'code',
                    new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
                    new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
                    'family_code',
                    ['family_label_1', 'family_label_2'],
                    'family_variant_code',
                    ['category_code_1', 'category_code_2'],
                    ['category_code_of_ancestors_1', 'category_code_of_ancestors_2'],
                    'parent_product_model_code',
                    [
                        'key1' => 'value1',
                        'key2' => 'value2',
                    ],
                    [
                        'ecommerce' => ['de_DE' => 1, 'fr_FR' => 0],
                        'mobile' => ['de_DE' => 1, 'fr_FR' => 0],
                    ],
                    [
                        'ecommerce' => ['de_DE' => 1, 'fr_FR' => 0],
                        'mobile' => ['de_DE' => 0, 'fr_FR' => 1],
                    ],
                    2,
                    ['<all_channels>' => ['en_US' => 'label']],
                    ['ancestor_attribute_code_1', 'ancestor_attribute_code_2'],
                    ['attribute_for_this_level1', 'attribute_for_this_level2'],
                    [
                        'foo' => 'bar',
                        'baz' => [
                            '42',
                            '44',
                        ],
                        'other_additional_data' => 'some data',
                    ]
                )
            );
    }
}
