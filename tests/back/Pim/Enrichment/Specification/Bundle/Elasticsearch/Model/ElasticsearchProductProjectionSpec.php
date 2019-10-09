<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductProjection;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ElasticsearchProductProjectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            '1',
            'identifier',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            true,
            'family_code',
            ['family_label_1', 'family_label_2'],
            'family_variant_code',
            ['category_code_1', 'category_code_2'],
            ['category_code_of_ancestors_1', 'category_code_of_ancestors_2'],
            ['group_code_1', 'group_code_2'],
            ['completeness_key' => 'completeness_value'],
            'parent_product_model_code',
            [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
            ['id_pm_1', 'id_pm_2'],
            ['code_pm_1', 'code_pm_2'],
            ['<all_channels>' => ['<all_locales>' => 'bar']],
            ['attribute_for_ancestor1'],
            ['attribute_for_this_level1', 'attribute_for_this_level2'],
            ['additional_key' => 'value']
        );
    }

    function it_is_an_elastic_search_projection()
    {
        $this->shouldBeAnInstanceOf(ElasticsearchProductProjection::class);
    }

    function it_can_be_converted_in_array()
    {
        $this->toArray()->shouldReturn([
            'id' => 'product_1',
            'identifier' => 'identifier',
            'created' => (new \DateTime('2019-04-23 15:55:50', new \DateTimeZone('UTC')))->format('c'),
            'updated' => (new \DateTime('2019-04-25 15:55:50', new \DateTimeZone('UTC')))->format('c'),
            'family' => [
                'code' => 'family_code',
                'labels' => ['family_label_1', 'family_label_2'],
            ],
            'enabled' => true,
            'categories' => ['category_code_1', 'category_code_2'],
            'categories_of_ancestors' => ['category_code_of_ancestors_1', 'category_code_of_ancestors_2'],
            'groups' => ['group_code_1', 'group_code_2'],
            'completeness' => ['completeness_key' => 'completeness_value'],
            'family_variant' => 'family_variant_code',
            'parent' => 'parent_product_model_code',
            'values' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
            'ancestors' => [
                'ids' => ['product_model_id_pm_1', 'product_model_id_pm_2'],
                'codes' => ['code_pm_1', 'code_pm_2'],
                'labels' => ['<all_channels>' => ['<all_locales>' => 'bar']],
            ],
            'label' => ['<all_channels>' => ['<all_locales>' => 'bar']],
            'document_type' => 'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
            'attributes_of_ancestors' => ['attribute_for_ancestor1'],
            'attributes_for_this_level' => ['attribute_for_this_level1', 'attribute_for_this_level2'],
            'in_group' => ['group_code_1' => true, 'group_code_2' => true],
            'additional_key' => 'value'
        ]);
    }

    function it_adds_additional_data()
    {
        $this->addAdditionalData(['key1' => 'values1'])
            ->addAdditionalData(['key2' => ['array']])->shouldBeLike(
            new ElasticsearchProductProjection(
                '1',
                'identifier',
                new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
                new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
                true,
                'family_code',
                ['family_label_1', 'family_label_2'],
                'family_variant_code',
                ['category_code_1', 'category_code_2'],
                ['category_code_of_ancestors_1', 'category_code_of_ancestors_2'],
                ['group_code_1', 'group_code_2'],
                ['completeness_key' => 'completeness_value'],
                'parent_product_model_code',
                [
                    'key1' => 'value1',
                    'key2' => 'value2',
                ],
                ['id_pm_1', 'id_pm_2'],
                ['code_pm_1', 'code_pm_2'],
                ['<all_channels>' => ['<all_locales>' => 'bar']],
                ['attribute_for_ancestor1'],
                ['attribute_for_this_level1', 'attribute_for_this_level2'],
                ['additional_key' => 'value', 'key1' => 'values1', 'key2' => ['array'],]
            )
        );
    }
}
