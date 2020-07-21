<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Elasticsearch\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;

final class GetProductModelIdsByAttributeOptionCodeQueryIntegration extends AbstractGetProductIdsByAttributeOptionCodeQueryIntegration
{
    public function test_it_returns_the_products_that_have_a_given_attribute_option()
    {
        $this->givenALocalizableMultiSelectAttributeWithOptions();

        $expectedProductModelIds[] = $this->createProductModel('a_product_model', [
            'a_localizable_multi_select' => [
                [
                    'scope' => null,
                    'locale' => 'en_US',
                    'data' => ['optionA', 'optionB']
                ],
            ],
        ]);
        $expectedProductModelIds[] = $this->createProductModel('another_product_model', [
            'a_localizable_multi_select' => [
                [
                    'scope' => null,
                    'locale' => 'en_US',
                    'data' => ['optionB']
                ],
                [
                    'scope' => null,
                    'locale' => 'fr_FR',
                    'data' => ['optionA']
                ],
            ],
        ]);
        $expectedProductModelIds[] = $this->createProductModel('a_sub_product_model', [
            'a_simple_select' => [
                [
                    'scope' => null,
                    'locale' => null,
                    'data' => 'optionB'
                ],
            ]
        ], 'a_product_model');

        $this->createProductModel('foo', [
            'a_localizable_multi_select' => [
                [
                    'scope' => null,
                    'locale' => 'en_US',
                    'data' => ['optionB']
                ],
            ],
        ]);
        $this->createProduct([
            'a_localizable_multi_select' => [
                [
                    'scope' => null,
                    'locale' => 'fr_FR',
                    'data' => ['optionA']
                ],
            ],
        ]);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $attributeOptionCode = new AttributeOptionCode(new AttributeCode('a_localizable_multi_select'), 'optionA');
        $productModelIds = iterator_to_array(
            $this->get('akeneo.pim.automation.data_quality_insights.query.get_product_model_ids_by_attribute_option_code')
            ->execute($attributeOptionCode, 2)
        );

        $this->assertCount(2, $productModelIds);
        $this->assertCount(2, $productModelIds[0]);
        $this->assertCount(1, $productModelIds[1]);

        $productModelIds = array_merge(...$productModelIds);
        $this->assertEqualsCanonicalizing($expectedProductModelIds, $productModelIds);
    }
}
