<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Import\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;

/**
 * +--------------+-----------------------------------+
 * |  Categories  |     Redactor    |     Manager     |
 * +--------------+-----------------------------------+
 * |    master    | View,Edit,Owner | View,Edit,Owner |
 * |  categoryA   |    View,Edit    | View,Edit,Owner |
 * |  categoryA1  |      View       | View,Edit,Owner |
 * |  categoryB   |        -        | View,Edit,Owner |
 * +--------------+-----------------------------------+
 *
 * +-------------------------------------------------------+-----------------------+
 * |                     Attributes                        |  Redactor |  Manager  |
 * +-------------------------------------------------------+-----------------------+
 * |  a_localized_and_scopable_text_area (attributeGroupA) | View,Edit | View,Edit |
 * |  a_text (attributeGroupA)                             | View,Edit | View,Edit |
 * |  a_multi_select (attributeGroupC)                     |     -     | View,Edit |
 * +-------------------------------------------------------+-----------------------+
 *
 * Check that a product model and its descendants are correctly indexed after a product model import with restricted
 * permissions
 */
class ComputeProductModelDescendantsIntegration extends AbstractProductModelImportTestCase
{
    /**
     * Checks that non viewable values/categories are still indexed after a product model import
     */
    function testItCorrectlyIndexesProductModelsAfterAnImportWithRestrictedPermissions()
    {
        $content = <<<CSV
code;family_variant;parent;categories;a_localized_and_scopable_text_area-en_US-ecommerce;a_text
root_product_model;familyVariantA1;;categoryA;"Lorem ipsum dolor sit amet";
sub_product_model;familyVariantA1;root_product_model;categoryA1;;"Some random text"
CSV;
        $this->jobLauncher->launchAuthenticatedSubProcessImport('csv_product_model_import', $content, 'mary');

        $this->assertProductModelIndex(
            [
                [
                    'field'    => 'categories',
                    'operator' => Operators::IN_LIST,
                    'value'    => ['categoryB'],
                ],
                [
                    'field' => 'a_multi_select',
                    'operator' => Operators::IN_LIST,
                    'value' => ['optionA'],
                ],
                [
                    'field' => 'a_localized_and_scopable_text_area',
                    'operator' => Operators::CONTAINS,
                    'value' => 'ipsum',
                    'context' => [
                        'scope'  => 'ecommerce',
                        'locale' => 'en_US',
                    ],
                ],
            ],
            [
                'root_product_model',
                'sub_product_model',
            ]
        );

        $this->assertProductModelIndex(
            [
                [
                    'field'    => 'categories',
                    'operator' => Operators::IN_LIST,
                    'value'    => ['categoryA1'],
                ],
                [
                    'field' => 'a_simple_select',
                    'operator' => Operators::IN_LIST,
                    'value' => ['optionA'],
                ],
                [
                    'field' => 'a_text',
                    'operator' => Operators::CONTAINS,
                    'value' => 'random',
                ]
            ],
            [
                'sub_product_model',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures();
    }

    /**
     * @param array $filters
     * @param array $codes
     */
    private function assertProductModelIndex(array $filters, array $codes): void
    {
        $pmqb = $this->get('pim_catalog.query.product_model_query_builder_factory')->create(
            [
                'filters' => $filters,
            ]
        );
        $cursor = $pmqb->execute();

        static::assertEquals($cursor->count(), count($codes));
        foreach ($cursor as $productModel) {
            static::assertContains($productModel->getCode(), $codes);
        }
    }

    private function loadFixtures(): void
    {
        $this->createProductModel(
            [
                'code'           => 'root_product_model',
                'family_variant' => 'familyVariantA1',
                'values'         => [
                    'a_multi_select' => [
                        [
                            'data'   => ['optionA', 'optionB'],
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                ],
                'categories'     => [
                    'categoryA',
                    'categoryB',
                ],
            ]
        );
        $this->createProductModel(
            [
                'code'           => 'sub_product_model',
                'family_variant' => 'familyVariantA1',
                'parent'         => 'root_product_model',
                'values'         => [
                    'a_simple_select' => [
                        [
                            'data'   => 'optionA',
                            'locale' => null,
                            'scope'  => null,
                        ],
                    ],
                ],
            ]
        );
        $this->createProduct(
            'variant_product',
            'familyA',
            [
                'enabled' => true,
                'parent'  => 'sub_product_model',
                'values'  => [
                    'a_yes_no' => [
                        [
                            'data'   => true,
                            'scope'  => null,
                            'locale' => null,
                        ],
                    ],
                ],
            ]
        );
    }
}
