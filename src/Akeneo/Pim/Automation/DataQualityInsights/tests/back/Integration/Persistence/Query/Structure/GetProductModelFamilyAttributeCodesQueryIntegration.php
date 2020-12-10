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
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

class GetProductModelFamilyAttributeCodesQueryIntegration extends DataQualityInsightsTestCase
{
    public function test_that_it_selects_the_family_attribute_codes_of_a_given_product()
    {
        $rootPm = $this->createProductModel('root_pm', 'familyVariantA1');
        $subPm = $this->createSubProductModel('sub_pm_A', 'familyVariantA1', 'root_pm',
            [
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

        // That will exclude the attribute "a_multi_select"
        $this->createAttributeGroupActivation('attributeGroupC', false);
        $this->createAttributeGroupActivation('attributeGroupB', true);

        $attributeCodes = $this
            ->get(GetProductModelFamilyAttributeCodesQuery::class)
            ->execute(new ProductId($rootPm->getId()));

        $expectedRootLevelAttributes = [
            new AttributeCode('a_date'),
            new AttributeCode('a_file'),
            new AttributeCode('a_localizable_image'),
            new AttributeCode('a_localized_and_scopable_text_area'),
            new AttributeCode('a_metric'),
            new AttributeCode('a_number_float'),
            new AttributeCode('a_number_float_negative'),
            new AttributeCode('a_number_integer'),
            new AttributeCode('a_price'),
            new AttributeCode('a_ref_data_multi_select'),
            new AttributeCode('a_ref_data_simple_select'),
            new AttributeCode('a_scopable_price'),
            new AttributeCode('an_image'),
        ];
        $this->assertEqualsCanonicalizing($expectedRootLevelAttributes, $attributeCodes);

        $attributeCodes = $this
            ->get(GetProductModelFamilyAttributeCodesQuery::class)
            ->execute(new ProductId($subPm->getId()));

        $expectedSubLevelAttributes = [
            new AttributeCode('a_date'),
            new AttributeCode('a_file'),
            new AttributeCode('a_localizable_image'),
            new AttributeCode('a_localized_and_scopable_text_area'),
            new AttributeCode('a_metric'),
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

        $this->assertEqualsCanonicalizing($expectedSubLevelAttributes, $attributeCodes);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
