<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\ProductModel\ExternalApi;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Test\Integration\Configuration;

/**
 * @group ce
 */
class ListProductModelWithCompletenessEndToEnd extends AbstractProductModelTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->createCompleteProductModelAndVariantProduct();
        $this->createUnCompleteProductModelAndVariantProduct();
    }

    public function testPaginationWithCompletenessFilter()
    {
        $client = $this->createAuthenticatedClient();

        $search = '{"completeness":[{"operator":"ALL COMPLETE","scope":"ecommerce","locales":["en_US"]}]}';
        $client->request('GET', 'api/rest/v1/product-models?locales=en_US&limit=2&search=' . $search);
        $searchEncoded = $this->encodeStringWithSymfonyUrlGeneratorCompatibility($search);
        $expected = <<<JSON
{
    "_links": {
        "self": {"href": "http://localhost/api/rest/v1/product-models?page=1&with_count=false&pagination_type=page&limit=2&search=${searchEncoded}&locales=en_US"},
        "first": {"href": "http://localhost/api/rest/v1/product-models?page=1&with_count=false&pagination_type=page&limit=2&search=${searchEncoded}&locales=en_US"}
    },
    "current_page" : 1,
    "_embedded"    : {
		"items": [
		    {
		        "_links":{
		            "self":{
		                "href": "http:\/\/localhost\/api\/rest\/v1\/product-models\/product_model_complete"
		            }
		        },
		        "code": "product_model_complete",
		        "parent": null,
		        "family": "familyA3",
		        "family_variant": "familyVariantA3_by_a_yes_no",
		        "categories": [],
		        "values": {
		            "a_localized_and_scopable_text_area": [
                        {"locale": "en_US", "scope": "ecommerce", "data": "this is a test"}
                    ],
                    "a_simple_select": [
                        {"locale": null, "scope": null, "data": "optionA"}
                    ]
		        },
		        "created": "2017-03-17T16:11:46+01:00",
		        "updated": "2017-03-17T16:11:46+01:00",
		        "associations": {
                    "PACK": {
                        "products": [],
                        "product_models": [],
                        "groups": []
                    },
                    "SUBSTITUTION": {
                        "products": [],
                        "product_models": [],
                        "groups": []
                    },
                    "UPSELL": {
                        "products": [],
                        "product_models": [],
                        "groups": []
                    },
                    "X_SELL": {
                        "products": [],
                        "product_models": [],
                        "groups": []
                    }
                },
                "quantified_associations": {}
		    }
		]
    }
}
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createCompleteProductModelAndVariantProduct(){
        $this->createFamilyVariant(
            [
                'code' => 'familyVariantA3_by_a_yes_no',
                'family' => 'familyA3',
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'axes' => ['a_yes_no'],
                        'attributes' => ['a_yes_no', 'a_text'],
                    ]
                ]
            ]
        );

        // a product model
        $this->createProductModel(
            [
                'code' => 'product_model_complete',
                'parent' => '',
                'family_variant' => 'familyVariantA3_by_a_yes_no',
                'values'  => [
                    'a_localized_and_scopable_text_area' => [
                        [
                            'locale' => 'en_US',
                            'scope' => 'ecommerce',
                            'data' => 'this is a test',
                        ],
                    ],
                    'a_simple_select' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => 'optionA',
                        ],
                    ],
                ]
            ]
        );

        // product complete, whatever the scope
        $this->createVariantProduct('sku-product-complete', [
            new SetCategories(['categoryA', 'categoryB', 'master']),
            new ChangeParent('product_model_complete'),
            new SetBooleanValue('a_yes_no', null, null, false),
            new SetIdentifierValue('sku', 'sku-product-complete')
        ]);
    }

    private function createUncompleteProductModelAndVariantProduct(){
        $this->createFamilyVariant(
            [
                'code' => 'familyVariantA3_by_a_simple_select',
                'family' => 'familyA3',
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'axes' => ['a_simple_select'],
                        'attributes' => ['a_simple_select', 'a_text'],
                    ]
                ]
            ]
        );

        // a product model
        $this->createProductModel(
            [
                'code' => 'product_model_uncomplete',
                'parent' => '',
                'family_variant' => 'familyVariantA3_by_a_simple_select',
                'values'  => [
                    'a_localized_and_scopable_text_area' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => null,
                        ],
                    ],
                    'a_yes_no' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => false
                        ],
                    ],
                ]
            ]
        );

        // product complete, whatever the scope
        $this->createVariantProduct('sku-product-uncomplete', [
            new SetCategories(['categoryA', 'categoryB', 'master']),
            new ChangeParent('product_model_uncomplete'),
            new SetSimpleSelectValue('a_simple_select', null, null, 'optionB'),
            new SetIdentifierValue('sku', 'sku-product-uncomplete')
        ]);
    }


    /**
     * @param array  $data
     *
     * @return FamilyVariantInterface
     * @throws \Exception
     */
    protected function createFamilyVariant(array $data = []) : FamilyVariantInterface
    {
        $family = $this->get('pim_catalog.factory.family_variant')->create();
        $this->get('pim_catalog.updater.family_variant')->update($family, $data);
        $constraintList = $this->get('validator')->validate($family);
        $this->assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.family_variant')->save($family);

        return $family;
    }
}
