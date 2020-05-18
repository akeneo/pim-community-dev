<?php

namespace AkeneoTestEnterprise\Pim\Enrichment\Product\EndToEnd\InternalAPI;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use AkeneoTest\Pim\Enrichment\EndToEnd\InternalApiTestCase;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class DuplicateProductEndToEnd extends InternalApiTestCase
{
    /** @var RouterInterface */
    private $router;

    protected function setUp(): void
    {
        parent::setUp();
        $this->router = $this->get('router');

        $adminUser = $this->get('pim_user.repository.user')->findOneByIdentifier('admin');
        $this->authenticate($adminUser);
    }

    public function test_it_duplicates_a_product_without_unique_values()
    {
        $familyCode = 'familyTest';
        $uniqueAttributeCodes = ['unique_attribute_1', 'unique_attribute_2'];
        $this->createAttributes($this->getAttributeData());
        $this->addAttributesToFamily($familyCode, $uniqueAttributeCodes);
        $associatedProduct = $this->createProduct('associated_product', [
            'family' => 'familyA1'
        ]);
        $normalizedProductToDuplicate = $this->getNormalizedProductToDuplicate($familyCode, $associatedProduct->getIdentifier());
        $productToDuplicate = $this->createProduct(
            'product_to_duplicate',
            $normalizedProductToDuplicate
        );

        $url = $this->router->generate('pimee_enrich_product_rest_duplicate', [
            'id' => $productToDuplicate->getId()
        ]);

        $this->client->request(
            'POST',
            $url,
            ['duplicated_product_identifier' => 'duplicated_product'],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals(
            ['unique_attribute_codes' => $uniqueAttributeCodes],
            json_decode($this->client->getResponse()->getContent(), true)
        );

        $duplicatedProduct = $this->get('pim_catalog.repository.product_without_permission')->findOneByIdentifier('duplicated_product');
        Assert::assertNotNull($duplicatedProduct);

        $normalizedDuplicatedProduct = $this->get('pim_catalog.normalizer.standard.product')->normalize(
            $duplicatedProduct,
            'standard'
        );
        $this->assertSameData($normalizedDuplicatedProduct, $normalizedProductToDuplicate, $uniqueAttributeCodes);
    }

    public function test_it_duplicates_a_product_without_family()
    {
        $productToDuplicate = $this->createProduct(
            'product_to_duplicate',
            []
        );

        $url = $this->router->generate('pimee_enrich_product_rest_duplicate', [
            'id' => $productToDuplicate->getId()
        ]);

        $this->client->request(
            'POST',
            $url,
            ['duplicated_product_identifier' => 'duplicated_product'],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function test_it_validates_the_duplicated_product()
    {
        $productToDuplicate = $this->createProduct(
            'product_to_duplicate',
            ['family' => 'familyA']
        );
        $url = $this->router->generate('pimee_enrich_product_rest_duplicate', [
            'id' => $productToDuplicate->getId()
        ]);

        $this->client->request(
            'POST',
            $url,
            ['duplicated_product_identifier' => $productToDuplicate->getIdentifier()],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ]
        );

        Assert::assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals(
            [
              'values' => [
                  [
                    'attribute' => 'sku',
                    'locale' => null,
                    'scope' => null,
                    'message' => 'The same identifier is already set on another product'
                  ]
              ]
            ],
            json_decode($this->client->getResponse()->getContent(), true)
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createAttributes(array $attributesData): void
    {
        $attributes = array_map(function ($data) {
            $attribute = $this->get('pim_catalog.factory.attribute')->createAttribute(AttributeTypes::TEXT);
            $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
            $errors = $this->get('validator')->validate($attribute);
            if (0 !== $errors->count()) {
                throw new \Exception(sprintf(
                    'Impossible to setup test in %s: %s',
                    static::class,
                    $errors->get(0)->getMessage()
                ));
            }

            return $attribute;
        }, $attributesData);

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);
    }

    private function addAttributesToFamily(string $familyCode, array $attributeCodes): void
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update(
            $family,
            [
                'code' => $familyCode,
                'attributes'  =>  $attributeCodes,
                'attribute_requirements' => [],
            ]
        );

        $errors = $this->get('validator')->validate($family);
        if (0 !== $errors->count()) {
            throw new \Exception(sprintf(
                'Impossible to setup test in %s: %s',
                static::class,
                $errors->get(0)->getMessage()
            ));
        }

        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function createProduct($identifier, array $data = []): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);

        $errors = $this->get('pim_catalog.validator.product')->validate($product);
        if (0 !== $errors->count()) {
            throw new \Exception(sprintf(
                'Impossible to setup test in %s: %s',
                static::class,
                $errors->get(0)->getMessage()
            ));
        }

        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $product;
    }

    private function assertSameData(
        array $normalizedDuplicatedProduct,
        array $normalizedProductToDuplicate,
        array $uniqueAttributeCodes
    ) {
        foreach($uniqueAttributeCodes as $attributeCode) {
            if (in_array($attributeCode, array_keys($normalizedProductToDuplicate['values']))) {
                unset($normalizedProductToDuplicate['values'][$attributeCode]);
            }
        }
        // Need to unset the 'sku' as its value is required and it couldn't be equal
        unset($normalizedProductToDuplicate['values']['sku']);
        unset($normalizedDuplicatedProduct['values']['sku']);

        Assert::assertEquals($normalizedProductToDuplicate['family'], $normalizedDuplicatedProduct['family']);
        Assert::assertEquals($normalizedProductToDuplicate['groups'], $normalizedDuplicatedProduct['groups']);
        Assert::assertEquals($normalizedProductToDuplicate['values'], $normalizedDuplicatedProduct['values']);
        Assert::assertEquals($normalizedProductToDuplicate['categories'], $normalizedDuplicatedProduct['categories']);
        Assert::assertEquals($normalizedProductToDuplicate['associations'], $normalizedDuplicatedProduct['associations']);
    }

    private function getNormalizedProductToDuplicate(string $familyCode, string $associatedProductIdentifier): array
    {
        return [
            'family' => $familyCode,
            'groups' => ['groupA', 'groupB'],
            'categories' => ['categoryA', 'categoryB'],
            'values' => [
                'sku' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'product_to_duplicate'
                    ]
                ],
                'unique_attribute_1' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'The first unique value'
                    ]
                ],
                'unique_attribute_2' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'The second unique value'
                    ]
                ],
                'non_unique_attribute' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'value non unique'
                    ]
                ]
            ],
            'associations' => [
                'PACK' => [
                    'groups' => [],
                    'product_models' => [],
                    'products' => [$associatedProductIdentifier]
                ],
                'UPSELL' => [
                    'groups' => ['groupA'],
                    'product_models' => [],
                    'products' => []
                ],
                'X_SELL' => [
                    'groups' => ['groupB'],
                    'product_models' => [],
                    'products' => [$associatedProductIdentifier]
                ],
                'SUBSTITUTION' => [
                    'groups' => [],
                    'product_models' => [],
                    'products' => []
                ]
            ]
        ];
    }

    private function getAttributeData()
    {
        return [
            [
                'code' => 'unique_attribute_1',
                'group' => 'other',
                'unique' => true,
            ],
            [
                'code' => 'unique_attribute_2',
                'group' => 'other',
                'unique' => true,
            ],
            [
                'code' => 'non_unique_attribute',
                'group' => 'other',
            ]
        ];
    }
}
