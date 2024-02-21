<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\InternalApi;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
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
                new SetImageValue(
                    'an_image',
                    null,
                    null,
                    $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')))
            ]
        );

        $url = $this->getRouter()->generate('pim_pdf_generator_download_product_pdf', [
            'uuid' => $product->getUuid()->toString(),
            'dataLocale' => 'en_US',
            'dataScope' => 'ecommerce',
        ]);

        $this->client->request('GET', $url);

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function test_it_downloads_a_pdf_for_a_product_with_an_image_with_uppercase_uuid(): void
    {
        $product = $this->createProduct(
            'simple',
            'familyA',
            [
                new SetImageValue(
                    'an_image',
                    null,
                    null,
                    $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')))
            ]
        );

        $url = $this->getRouter()->generate('pim_pdf_generator_download_product_pdf', [
            'uuid' => \strtoupper($product->getUuid()->toString()),
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
            'uuid' => $product->getUuid()->toString(),
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
        return self::getContainer()->get('router');
    }

    private function getAdminUser(): UserInterface
    {
        return self::getContainer()->get('pim_user.repository.user')->findOneByIdentifier('admin');
    }
}
