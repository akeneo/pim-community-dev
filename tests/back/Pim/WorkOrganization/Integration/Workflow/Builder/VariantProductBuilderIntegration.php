<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\Builder;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class VariantProductBuilderIntegration extends TestCase
{
    public function testBuildAProductDraftForAVariantProduct()
    {
        $variantProduct = $this->get('pim_catalog.repository.product')->findOneByIdentifier('my_variant_product');
        $productDraft = $this->get('pimee_workflow.repository.product_draft')->findUserEntityWithValuesDraft($variantProduct, 'mary');

        $this->assertEquals([
            'a_localized_and_scopable_text_area' => [
                ['data' => 'Modified US in draft', 'locale' => 'en_US', 'scope' => 'ecommerce'],
            ],
            'a_yes_no' => [
                ['data' => true, 'locale' => null, 'scope' => null],
            ]
        ], $productDraft->getChanges()['values']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        // create product model
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, [
            'code' => 'product_model',
            'family_variant' => 'familyVariantA1',
            'values' => [
                'a_multi_select' => [
                    ['data' => ['optionA', 'optionB'], 'locale' => null, 'scope' => null],
                ],
            ]
        ]);

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        // create variant product
        $product = $this->get('pim_catalog.builder.product')->createProduct('my_variant_product');
        $this->get('pim_catalog.updater.product')->update($product, [
            'categories' => ['categoryA'],
            'parent' => 'product_model',
            'values' => [
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'Unchanged US', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 'FR ecommerce', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                ]
            ]
        ]);
        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        // update the variant product
        $this->get('pim_catalog.updater.product')->update($product, [
            'values' => [
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'Modified US in draft', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                ],
                'a_yes_no' => [
                    ['data' => true, 'locale' => null, 'scope' => null],
                ],
            ]
        ]);

        $productDraft = $this->get('pimee_workflow.product.builder.draft')->build($product, 'mary');

        $this->get('pimee_workflow.saver.product_draft')->save($productDraft);
    }
}
