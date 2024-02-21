<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueValuesSet;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Test\IntegrationTestsBundle\Helper\AuthenticatorHelper;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client as EsClient;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractProductTestCase extends ApiTestCase
{
    /**
     * @param UserIntent[] $userIntents
     */
    protected function createProductWithUuid(string $uuid, array $userIntents = []): ProductInterface
    {
        $this->getAuthenticator()->logIn('admin');

        $command = UpsertProductCommand::createWithUuid(
            $this->getUserId('admin'),
            ProductUuid::fromUuid(Uuid::fromString($uuid)),
            $userIntents
        );

        $this->getMessageBus()->dispatch($command);

        return $this->getProductRepository()->find($uuid);
    }

    /**
     * @param UserIntent[] $userIntents
     */
    protected function createProductWithoutIdentifier(array $userIntents = []): ProductInterface
    {
        return $this->createProductWithUuid(Uuid::uuid4()->toString(), $userIntents);
    }

    /**
     * @param UserIntent[] $userIntents
     */
    protected function createProduct(string $identifier, array $userIntents = []): ProductInterface
    {
        $this->getAuthenticator()->logIn('admin');

        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $identifier,
            userIntents: $userIntents
        );

        $this->getMessageBus()->dispatch($command);

        return $this->getProductRepository()->findOneByIdentifier($identifier);
    }

    /**
     * @param UserIntent[] $userIntents
     */
    protected function createVariantProduct(string $identifier, array $userIntents = []): ProductInterface
    {
        $product = $this->createProduct($identifier, $userIntents);

        $this->clearAllCache();

        return $product;
    }

    protected function clearAllCache()
    {
        $this->getUniqueValueSetValidator()->reset();
        $this->getEsIndex()->refreshIndex();
        $this->getOrmCacheClearer()->clear();
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
    protected function createProductModel(array $data = []): ProductModelInterface
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
        $this->getEsIndex()->refreshIndex();

        return $productModel;
    }

    /**
     * In the context of response with UUIDs, the response changes each time you will run the tests because UUID
     * are never the same, and the results are sorted by uuids.
     * By setting the parameter $sortExpectedByUuid to true, you don't need to sort the expected response, it will
     * be automatically sorted by uuids.
     */
    protected function assertListResponse(Response $response, string $expected, $sortExpectedByUuid = false): void
    {
        $result = json_decode($response->getContent(), true);
        $expected = json_decode($expected, true, 512, JSON_THROW_ON_ERROR);

        if (!isset($result['_embedded'])) {
            Assert::fail($response->getContent());
        }

        foreach ($result['_embedded']['items'] as $index => $product) {
            NormalizedProductCleaner::clean($result['_embedded']['items'][$index]);

            if (isset($expected['_embedded']['items'][$index])) {
                NormalizedProductCleaner::clean($expected['_embedded']['items'][$index]);
            }
        }

        if ($sortExpectedByUuid) {
            usort($expected['_embedded']['items'], fn (array $p1, array $p2): int => \strcmp($p1['uuid'], $p2['uuid']));
        }

        Assert::assertEquals($expected, $result);
    }

    /**
     * @param array  $expectedProduct normalized data of the product that should be created
     * @param string $identifier identifier of the product that should be created
     */
    protected function assertSameProducts(array $expectedProduct, string $identifier): void
    {
        $this->getOrmCacheClearer()->clear();
        $product = $this->getProductRepository()->findOneByIdentifier($identifier);

        $standardizedProduct = $this->get('pim_standard_format_serializer')->normalize($product, 'standard');
        if (!isset($expectedProduct['uuid'])) {
            unset($standardizedProduct['uuid']);
        }

        NormalizedProductCleaner::clean($expectedProduct);
        NormalizedProductCleaner::clean($standardizedProduct);

        Assert::assertSame($expectedProduct, $standardizedProduct);
    }

    /**
     * Type aware service accessors below
     */

    private function getAuthenticator(): AuthenticatorHelper
    {
        return $this->get('akeneo_integration_tests.helper.authenticator');
    }

    private function getMessageBus(): MessageBusInterface
    {
        return $this->get('pim_enrich.product.message_bus');
    }

    private function getProductRepository(): ProductRepositoryInterface
    {
        return $this->get('pim_catalog.repository.product');
    }

    private function getUniqueValueSetValidator(): UniqueValuesSet
    {
        return $this->get('pim_catalog.validator.unique_value_set');
    }

    protected function getEsIndex(): EsClient
    {
        return $this->get('akeneo_elasticsearch.client.product_and_product_model');
    }

    private function getOrmCacheClearer(): EntityManagerClearerInterface
    {
        return $this->get('pim_connector.doctrine.cache_clearer');
    }
}
