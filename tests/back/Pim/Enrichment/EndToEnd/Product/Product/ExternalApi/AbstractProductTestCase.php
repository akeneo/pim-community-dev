<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractProductTestCase extends ApiTestCase
{
    public array $EMPTY_ASSOCIATIONS = [
        'PACK' => [
            'groups' => [],
            'product_models' => [],
            'products' => [],
        ],
        'SUBSTITUTION' => [
            'groups' => [],
            'product_models' => [],
            'products' => [],
        ],
        'UPSELL' => [
            'groups' => [],
            'product_models' => [],
            'products' => [],
        ],
        'X_SELL' => [
            'groups' => [],
            'product_models' => [],
            'products' => [],
        ]
    ];

    /**
     * @param UserIntent[] $userIntents
     */
    protected function createProduct(string $identifier, array $userIntents = []): ProductInterface
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $identifier,
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    /**
     * @param UserIntent[] $userIntents
     */
    protected function createVariantProduct(string $identifier, array $userIntents = []) : ProductInterface
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: $identifier,
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
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
    protected function assertSameProducts(array $expectedProduct, string $identifier)
    {
        $id = $this->getUserId('admin');
        $query = <<<SQL
            SELECT BIN_TO_UUID(uuid) as uuid FROM pim_catalog_product WHERE identifier = :identifier
        SQL;
        $uuid = $this->get('database_connection')->executeQuery($query, ['identifier' => $identifier])->fetchOne();
        // use same normalizer as in ExternalApi
        $product = $this->get('akeneo.pim.enrichment.product.connector.get_product_from_uuids')
            ->fromProductUuid(Uuid::fromString($uuid), intval($id));
        $standardizedProduct = $this->get('pim_api.normalizer.connector_products')->normalizeConnectorProduct($product);

        NormalizedProductCleaner::clean($standardizedProduct);
        NormalizedProductCleaner::clean($expectedProduct);

        $this->assertEquals($expectedProduct, $standardizedProduct);
    }

    protected function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        if (null === $id) {
            throw new \InvalidArgumentException(\sprintf('No user exists with username "%s"', $username));
        }

        return \intval($id);
    }
}
