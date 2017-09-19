<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Product;

use Akeneo\Test\Integration\Configuration;
use Symfony\Component\HttpFoundation\Response;

class DeleteVariantProductIntegration extends AbstractProductTestCase
{
    public function testDeleteAVariantProduct()
    {
        $client = $this->createAuthenticatedClient();

        $this->assertCount(247, $this->get('pim_catalog.repository.product')->findAll());

        $fooProduct = $this->get('pim_catalog.repository.product')->findOneByIdentifier('apollon_blue_xl');
        $this->get('pim_catalog.elasticsearch.indexer.product')->index($fooProduct);
        $client->request('DELETE', 'api/rest/v1/products/apollon_blue_xl');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $this->assertCount(246, $this->get('pim_catalog.repository.product')->findAll());
        $this->assertNull($this->get('pim_catalog.repository.product')->findOneByIdentifier('apollon_blue_xl'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return new Configuration([Configuration::getFunctionalCatalogPath('catalog_modeling')]);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $product = $this->get('pim_catalog.builder.variant_product')->createProduct('apollon_blue_xl', 'clothing');
        $this->get('pim_catalog.updater.product')->update($product, [
            'parent' => 'apollon_blue',
            'values' => [
                'size' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'xl',
                    ],
                ],
            ],
        ]);

        $errors = $this->get('pim_catalog.validator.product')->validate($product);
        if (0 !== $errors->count()) {
            throw new \Exception(sprintf(
                                     'Impossible to setup test in %s: %s',
                                     static::class,
                                     $errors->get(0)->getMessage()
                                 ));
        }

        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('pim_catalog.validator.unique_value_set')->reset();
    }

}
