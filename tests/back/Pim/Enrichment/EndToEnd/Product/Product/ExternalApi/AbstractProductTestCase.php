<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractProductTestCase extends ApiTestCase
{
    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     * @throws \Exception
     */
    protected function createProduct($identifier, array $data = [])
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

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     * @throws \Exception
     */
    protected function createVariantProduct($identifier, array $data = []) : ProductInterface
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

    /**
     * Each time we create a product model, a batch job is pushed into the queue to calculate the
     * completeness of its descendants.
     *
     * @param array $data
     *
     * @return ProductModelInterface
     * @throws \Exception
     */
    protected function createProductModel(array $data = []) : ProductModelInterface
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, $data);

        $errors = $this->get('pim_catalog.validator.product')->validate($productModel);
        if (0 !== $errors->count()) {
            throw new \Exception(sprintf(
                'Impossible to setup test in %s: %s',
                static::class,
                $errors->get(0)->getMessage()
            ));
        }
        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return $productModel;
    }

    /**
     * @param Response $response
     * @param string   $expected
     */
    protected function assertListResponse(Response $response, $expected)
    {
        $result = json_decode($response->getContent(), true);
        $expected = json_decode($expected, true);

        if (!isset($result['_embedded'])) {
            Assert::fail($response->getContent());
        }

        foreach ($result['_embedded']['items'] as $index => $product) {
            NormalizedProductCleaner::clean($result['_embedded']['items'][$index]);

            if (isset($expected['_embedded']['items'][$index])) {
                NormalizedProductCleaner::clean($expected['_embedded']['items'][$index]);
            }
        }

        Assert::assertEquals($expected, $result);
    }

    /**
     * @param array  $expectedProduct normalized data of the product that should be created
     * @param string $identifier identifier of the product that should be created
     */
    protected function assertSameProducts(array $expectedProduct, $identifier)
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);

        $standardizedProduct = $this->get('pim_standard_format_serializer')->normalize($product, 'standard');

        NormalizedProductCleaner::clean($expectedProduct);
        NormalizedProductCleaner::clean($standardizedProduct);

        Assert::assertSame($expectedProduct, $standardizedProduct);
    }
}
