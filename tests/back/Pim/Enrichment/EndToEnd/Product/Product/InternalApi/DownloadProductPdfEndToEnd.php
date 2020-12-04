<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\EndToEnd\InternalApiTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class DownloadProductPdfEndToEnd extends InternalApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticate($this->getAdminUser());
    }

    public function test_it_has_the_required_dependency_to_generate_a_pdf_with_an_image(): void
    {
        Assert::assertTrue(extension_loaded('gd'), 'The PHP GD extension is required, but is not installed.');
    }

    public function test_it_downloads_a_pdf_for_a_product_with_an_image(): void
    {
        $product = $this->createProduct(
            'simple',
            'familyA',
            [
                'values' => [
                    'an_image' => [
                        [
                            'locale' => null,
                            'scope' => null,
                            'data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')),
                        ],
                    ],
                ],
            ]
        );

        $url = $this->getRouter()->generate('pim_pdf_generator_download_product_pdf', [
            'id' => $product->getId(),
            'dataLocale' => 'en_US',
            'dataScope' => 'ecommerce',
        ]);

        $this->client->request('GET', $url);

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function test_it_downloads_a_pdf_for_a_product_without_family(): void
    {
        $product = $this->createProduct('simple', null, []);

        $url = $this->getRouter()->generate('pim_pdf_generator_download_product_pdf', [
            'id' => $product->getId(),
            'dataLocale' => 'en_US',
            'dataScope' => 'ecommerce',
        ]);

        $this->client->request('GET', $url);

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getRouter(): RouterInterface
    {
        return self::$container->get('router');
    }

    private function getAdminUser(): UserInterface
    {
        return self::$container->get('pim_user.repository.user')->findOneByIdentifier('admin');
    }

    private function createProduct(string $identifier, ?string $familyCode, array $data = []): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, $familyCode);
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
}
