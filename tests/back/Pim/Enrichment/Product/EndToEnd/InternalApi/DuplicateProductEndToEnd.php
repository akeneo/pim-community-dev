<?php

namespace AkeneoTestEnterprise\Pim\Enrichment\Product\EndToEnd\InternalAPI;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use AkeneoTest\Pim\Enrichment\EndToEnd\InternalApiTestCase;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class DuplicateProductEndToEnd extends InternalApiTestCase
{
    /** @var RouterInterface */
    private $router;

    /** @var UserInterface */
    private $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->router = $this->get('router');
        $this->adminUser = $this->get('pim_user.repository.user')->findOneByIdentifier('admin');

        $this->authenticate($this->adminUser);

    }

    public function test_it_duplicates_a_product_without_unique_values()
    {
        $associatedProduct = $this->createProduct('associated_product', [
            'family' => 'familyA1'
        ]);
        $normalizedProductToDuplicate = $this->getNormalizedProductToDuplicate($associatedProduct->getIdentifier());
        $productToDuplicate = $this->createProduct(
            'product_to_duplicate',
            $normalizedProductToDuplicate
        );

        $url = $this->router->generate('pimee_enrich_product_rest_duplicate', [
            'id' => $productToDuplicate->getId()
        ]);

        $this->client->request('POST', $url, ['duplicated_product_identifier' => 'duplicated_product']);

        $duplicatedProduct = $this->get('pim_catalog.repository.product_without_permission')->findOneByIdentifier('duplicated_product');

        Assert::assertNotNull($duplicatedProduct);
        $this->assertSameData($duplicatedProduct, $normalizedProductToDuplicate);
        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
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

    private function getNormalizedProductToDuplicate(string $associatedProductIdentifier): array
    {
        return [
            'family' => 'familyA',
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
                'a_text' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'An amazing product'
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

    private function assertSameData(ProductInterface $duplicatedProduct, array $normalizedProductToDuplicate): void
    {
        $normalizedDuplicatedProduct = $this->get('pim_catalog.normalizer.standard.product')->normalize(
            $duplicatedProduct,
            'standard'
        );

        $uniqueAttributeCodes = ['sku'];
        foreach($uniqueAttributeCodes as $attributeCode) {
            unset($normalizedProductToDuplicate['values'][$attributeCode]);
        }
        // Need to unset the 'sku' as its value is required and it coudn't be equal
        unset($normalizedDuplicatedProduct['values']['sku']);

        Assert::assertEquals($normalizedProductToDuplicate['family'], $normalizedDuplicatedProduct['family']);
        Assert::assertEquals($normalizedProductToDuplicate['groups'], $normalizedDuplicatedProduct['groups']);
        Assert::assertEquals($normalizedProductToDuplicate['values'], $normalizedDuplicatedProduct['values']);
        Assert::assertEquals($normalizedProductToDuplicate['categories'], $normalizedDuplicatedProduct['categories']);
        Assert::assertEquals($normalizedProductToDuplicate['associations'], $normalizedDuplicatedProduct['associations']);
    }
}
