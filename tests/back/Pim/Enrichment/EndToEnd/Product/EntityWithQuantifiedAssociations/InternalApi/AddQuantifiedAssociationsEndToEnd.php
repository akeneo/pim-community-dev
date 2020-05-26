<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use AkeneoTest\Pim\Enrichment\EndToEnd\InternalApiTestCase;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class AddQuantifiedAssociationsEndToEnd extends InternalApiTestCase
{
    /** @var ProductInterface */
    private $product;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->authenticate($this->getAdminUser());
        $this->createQuantifiedAssociationType('PRODUCTSET');
        $this->product = $this->createProduct([
            'values' => [
                'sku' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'data' => 'yellow_chair',
                    ],
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    /**
     * @test
     */
    public function it_add_quantified_associations_to_a_product(): void
    {
        $productId = $this->product->getId();
        $normalizedProduct = $this->getProductFromInternalApi($productId);

        $quantifiedAssociations = [
            'PRODUCTSET' => [
                'products' => [
                    [
                        'identifier' => '1111111111',
                        'quantity' => 3,
                    ],
                ],
                'product_models' => [
                    [
                        'identifier' => 'amor',
                        'quantity' => 42,
                    ],
                ],
            ],
        ];

        $normalizedProductWithQuantifiedAssociations = $this->updateNormalizedProduct($normalizedProduct, [
            'quantified_associations' => $quantifiedAssociations,
        ]);

        $this->client->request(
            'POST',
            sprintf('/enrich/product/rest/%s', $productId),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            json_encode($normalizedProductWithQuantifiedAssociations)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $body = json_decode($response->getContent(), true);
        $this->assertSame($body['quantified_associations'], $quantifiedAssociations);
    }

    private function getProductFromInternalApi($productId): array
    {
        $this->client->request(
            'GET',
            sprintf('/enrich/product/rest/%s', $productId),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        return json_decode($this->client->getResponse()->getContent(), true);
    }

    private function createProduct(array $fields): ProductInterface
    {
        $product = new Product();
        $this->getProductUpdater()->update($product, $fields);
        $this->getProductSaver()->save($product);

        return $product;
    }

    private function createQuantifiedAssociationType(string $code): void
    {
        $data =
            <<<JSON
    {
        "code": "$code",
        "is_quantified": true
    }
JSON;
        $this->client->request(
            'POST',
            '/configuration/association-type/rest',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            $data
        );
    }

    private function updateNormalizedProduct(array $data, array $changes): array
    {
        unset($data['meta']);

        return array_merge_recursive($data, $changes);
    }

    private function getProductSaver(): SaverInterface
    {
        return self::$container->get('pim_catalog.saver.product');
    }

    private function getProductUpdater(): ObjectUpdaterInterface
    {
        return self::$container->get('pim_catalog.updater.product');
    }

    private function getAdminUser(): UserInterface
    {
        return self::$container->get('pim_user.repository.user')->findOneByIdentifier('admin');
    }
}
