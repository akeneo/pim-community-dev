<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\Command;

use Akeneo\Pim\Enrichment\Bundle\Command\CleanRemovedProductsCommand;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Model\ElasticsearchProductProjection;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\CommandLauncher;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Console\Command\Command;

class CleanRemovedProductsCommandEndToEnd extends TestCase
{
    protected Command $command;
    protected Client $esProductClient;

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        //Create a product
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: 'existing_product_in_db',
            userIntents: []
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $this->esProductClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
        /*$productUuid = $this->getProductUuidFromIdentifier('existing_product_in_db');
        $this->get('akeneo.pim.enrichment.product.query.get_elasticsearch_product_projection')->fromProductUuids([$productUuid]);
        $this->esProductClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');*/
        // $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    public function test_getProduct_Existing_in_db(): void
    {
        $product = 'existing_product_in_db';
        $query = <<<SQL
            SELECT BIN_TO_UUID(uuid) AS uuid FROM pim_catalog_product WHERE identifier = :productIdentifier
        SQL;

        //retrieve data from db
        $stmt = $this->get('database_connection')->executeQuery($query, ['productIdentifier' => $product]);
        $id = $stmt->fetchOne();
        Assert::assertNotNull($id);
        $idDB = ElasticsearchProductProjection::INDEX_PREFIX_ID . $id;

        //retrieve data from Elasticsearch
        $params = [
            'query' => [
                'match' => [
                        'identifier' => $product
                ]
            ]
        ];
        $this->esProductClient->refreshIndex();
        $result = $this->esProductClient->search($params);
        Assert::assertNotNull($result);
        $doc = $result["hits"]["hits"];
        foreach ($doc as $information) {
            $docId = $information["_source"]["id"];
        }

        Assert::assertEquals($idDB, $docId);
    }

    public function test_getProduct_Not_Existing_In_DB(): void
    {
        //create a product only in Elasticsearch
        $product = [
            'identifier' => 'product_not_exiting_product_in_db',
            'values'     => [
                'name-text' => [
                    '<all_channels>' => [
                        '<all_locales>' => '2015/01/01',
                    ],
                ],
            ]
        ];
        $this->esProductClient->index($product['identifier'], $product);

        $this->esProductClient->refreshIndex();
        $params = [
            'query' => [
                'match' => [
                    'identifier' => $product['identifier']
                ]
            ]
        ];
        $result = $this->esProductClient->search($params);
        $doc = $result["hits"]["hits"];
        foreach ($doc as $information) {
            $identifierDoc = $information["_source"]["identifier"];
        }

        //search in DB
        $query = <<<SQL
            SELECT identifier FROM pim_catalog_product WHERE identifier = :productIdentifier
        SQL;

        //retrieve data from db
        $stmt = $this->get('database_connection')->executeQuery($query, ['productIdentifier' => $product['identifier']]);
        $identifier = $stmt->fetchOne();
        $identifierDB = ElasticsearchProductProjection::INDEX_PREFIX_ID . $identifier;

        Assert::assertNotEquals($identifierDoc, $identifierDB);

    }
    private function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        Assert::assertNotNull($id);

        return \intval($id);
    }

    private function getProductUuidFromIdentifier(string $productIdentifier): UuidInterface
    {
        return Uuid::fromString($this->get('database_connection')->fetchOne(
            'SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product WHERE identifier = ?', [$productIdentifier]
        ));
    }
}
