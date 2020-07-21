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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetProductModelFamilyAttributeCodesQuery;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;

class GetProductModelFamilyAttributeCodesQueryIntegration extends TestCase
{
    public function test_that_it_selects_the_family_attribute_codes_of_a_given_product()
    {
        $rootPm = $this->createProductModel(
            [
                'code' => 'root_pm',
                'family_variant' => 'familyVariantA1',
                'values' => [],
            ]
        );
        $subPm = $this->createProductModel(
            [
                'code' => 'sub_pm_A',
                'family_variant' => 'familyVariantA1',
                'parent' => 'root_pm',
                'values' => [
                    'a_simple_select' => [
                        'data' => [
                            'data' => 'optionA',
                            'locale' => null,
                            'scope' => null,
                        ],
                    ],
                    'a_text' => [
                        [
                            'data' => 'some text',
                            'locale' => null,
                            'scope' => null,
                        ],
                    ],
                ],
            ]
        );

        $attributeCodes = $this
            ->get(GetProductModelFamilyAttributeCodesQuery::class)
            ->execute(new ProductId($rootPm->getId()));
        usort($attributeCodes, function (AttributeCode $attributeCode1, AttributeCode $attributeCode2) {
            return strcmp(strval($attributeCode1), strval($attributeCode2));
        });

        $expectedRootLevelAttributes = [
            new AttributeCode('a_date'),
            new AttributeCode('a_file'),
            new AttributeCode('a_localizable_image'),
            new AttributeCode('a_localized_and_scopable_text_area'),
            new AttributeCode('a_metric'),
            new AttributeCode('a_multi_select'),
            new AttributeCode('a_number_float'),
            new AttributeCode('a_number_float_negative'),
            new AttributeCode('a_number_integer'),
            new AttributeCode('a_price'),
            new AttributeCode('a_ref_data_multi_select'),
            new AttributeCode('a_ref_data_simple_select'),
            new AttributeCode('a_scopable_price'),
            new AttributeCode('an_image'),
        ];
        $this->assertEquals($expectedRootLevelAttributes, $attributeCodes);

        $attributeCodes = $this
            ->get(GetProductModelFamilyAttributeCodesQuery::class)
            ->execute(new ProductId($subPm->getId()));
        usort($attributeCodes, function (AttributeCode $attributeCode1, AttributeCode $attributeCode2) {
            return strcmp(strval($attributeCode1), strval($attributeCode2));
        });

        $expectedSubLevelAttributes = [
            new AttributeCode('a_date'),
            new AttributeCode('a_file'),
            new AttributeCode('a_localizable_image'),
            new AttributeCode('a_localized_and_scopable_text_area'),
            new AttributeCode('a_metric'),
            new AttributeCode('a_multi_select'),
            new AttributeCode('a_number_float'),
            new AttributeCode('a_number_float_negative'),
            new AttributeCode('a_number_integer'),
            new AttributeCode('a_price'),
            new AttributeCode('a_ref_data_multi_select'),
            new AttributeCode('a_ref_data_simple_select'),
            new AttributeCode('a_scopable_price'),
            new AttributeCode('a_simple_select'),
            new AttributeCode('a_text'),
            new AttributeCode('an_image'),
        ];

        $this->assertEquals($expectedSubLevelAttributes, $attributeCodes);
    }

    private function createProductModel(array $data): ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        $errors = $this->get('pim_catalog.validator.product')->validate($productModel);
        if (0 !== $errors->count()) {
            throw new \Exception(
                sprintf(
                    'Impossible to setup test in %s: %s',
                    static::class,
                    $errors->get(0)->getMessage()
                )
            );
        }
        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
