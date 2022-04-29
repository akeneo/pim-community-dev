<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\MigrateToUuidAddTriggers;
use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\MigrateToUuidStep;
use Akeneo\Pim\Enrichment\Component\Comment\Model\Comment;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\UserManagement\Component\Model\UserInterface;
use AkeneoTest\Pim\Enrichment\Integration\Product\UuidMigration\AbstractMigrateToUuidTestCase;
use Doctrine\Common\Util\ClassUtils;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

// Whole class to delete ?
final class MigrateToUuidCommandIntegration extends AbstractMigrateToUuidTestCase
{
    private UserInterface $adminUser;

    /** @test */
    public function it_migrates_the_database_to_use_uuid(): void
    {
        $this->clean();
        $this->loadFixtures();

        $this->assertTheIndexesDoNotExist();
        $this->launchMigrationCommand();
        $this->assertTheIndexesExist();
        $this->assertAllProductsHaveUuid();
        $this->assertJsonHaveUuid();
        $this->assertTriggersExistAndWork();
        $this->assertProductsAreReindexed();
        $this->assertColumnsAreNullable();

        // check that the migration can be launched twice without error
        $this->launchMigrationCommand();
    }

    private function assertTheIndexesDoNotExist(): void
    {
        $tables = \array_filter(
            MigrateToUuidStep::TABLES,
            fn (string $tableName): bool => $tableName !== 'pim_catalog_product',
            ARRAY_FILTER_USE_KEY
        );

        foreach ($tables as $tableName => $tableProperties) {
            $indexName = $tableProperties[MigrateToUuidStep::UUID_COLUMN_INDEX_NAME_INDEX];
            if (null !== $indexName && $this->tableExists($tableName)) {
                Assert::assertFalse(
                    $this->indexExists($tableName, $indexName),
                    \sprintf(
                        'The "%s" index exists in the "%s" table',
                        $indexName,
                        $tableName
                    )
                );
            }
        }
    }

    private function assertTheIndexesExist(): void
    {
        foreach (MigrateToUuidStep::TABLES as $tableName => $tableProperties) {
            $indexName = $tableProperties[MigrateToUuidStep::UUID_COLUMN_INDEX_NAME_INDEX];
            if (null !== $indexName && $this->tableExists($tableName)) {
                Assert::assertTrue(
                    $this->indexExists($tableName, $indexName),
                    \sprintf(
                        'The "%s" index does not exist in the "%s" table',
                        $indexName,
                        $tableName
                    )
                );
            }
        }
    }

    private function assertAllProductsHaveUuid(): void
    {
        $query = 'SELECT COUNT(*) FROM pim_catalog_product WHERE uuid IS NULL';

        $result = (int)$this->connection->executeQuery($query)->fetchOne();

        Assert::assertSame(0, $result, \sprintf('%s product(s) does not have an uuid after migration.', $result));
    }

    private function assertJsonHaveUuid(): void
    {
        $query = 'SELECT BIN_TO_UUID(uuid) AS uuid, quantified_associations FROM pim_catalog_product ORDER BY id ASC';

        $result = $this->connection->fetchAllAssociative($query);

        foreach (range(1, 10) as $i) {
            $quantifiedAssociations = \json_decode($result[$i - 1]['quantified_associations'], true);
            if ($i === 1) {
                // the first product is linked to a non existing product and is cleaned
                Assert::assertEquals(["SOIREEFOOD10" => ["products" => []]], $quantifiedAssociations);
            } else {
                Assert::assertEquals([
                    "SOIREEFOOD10" => [
                        "products" => [
                            ['id' => $i - 1, 'uuid' => $result[$i - 2]['uuid'], 'quantity' => 1000],
                        ],
                    ],
                ], $quantifiedAssociations);
            }
        }
    }

    private function assertTriggersExistAndWork(): void
    {
        foreach (\array_keys(MigrateToUuidStep::TABLES) as $tableName) {
            if ($tableName === 'pim_catalog_product' || !$this->tableExists($tableName)) {
                continue;
            }

            $insertTriggerName = MigrateToUuidAddTriggers::getInsertTriggerName($tableName);
            Assert::assertTrue(
                $this->triggerExists($insertTriggerName),
                \sprintf('The %s trigger does not exist', $insertTriggerName)
            );
            $updateTriggerName = MigrateToUuidAddTriggers::getUpdateTriggerName($tableName);
            Assert::assertTrue(
                $this->triggerExists($updateTriggerName),
                \sprintf('The %s trigger does not exist', $updateTriggerName)
            );
        }

        /**
         * Create product
         */
        $product = $this->get('pim_catalog.builder.product')->createProduct('new_product');
        $this->get('pim_catalog.updater.product')->update($product, [
            'categories' => ['master'],
            'associations' => ['X_SELL' => ['products' => ['identifier1']]],
            'groups' => ['groupA'],
        ]);
        $this->get('pim_catalog.validator.product')->validate($product);
        $this->get('pim_catalog.saver.product')->save($product);

        $newProductId = $product->getId();
        Assert::assertNotNull($newProductId);
        $newProductUuid = $this->getProductUuid('new_product');

        // pim_catalog_association
        $ownerdUuids = $this->connection->executeQuery(
            'SELECT DISTINCT BIN_TO_UUID(owner_uuid) FROM pim_catalog_association'
        )->fetchFirstColumn();
        Assert::assertSame([$newProductUuid], $ownerdUuids);
        // pim_catalog_association_product
        Assert::assertTrue(
            (bool)$this->connection->executeQuery(
                'SELECT EXISTS (SELECT 1 FROM pim_catalog_association_product WHERE product_uuid = UUID_TO_BIN(?)) AS e',
                [$this->getProductUuid('identifier1')]
            )->fetchOne()
        );
        // pim_catalog_category_product
        Assert::assertSame(
            [$newProductUuid],
            $this->connection->executeQuery(
                'SELECT DISTINCT BIN_TO_UUID(product_uuid) FROM pim_catalog_category_product WHERE product_id = ?',
                [$newProductId]
            )->fetchFirstColumn()
        );
        // pim_catalog_group_product
        Assert::assertSame(
            [$newProductUuid],
            $this->connection->executeQuery(
                'SELECT DISTINCT BIN_TO_UUID(product_uuid) FROM pim_catalog_group_product WHERE product_id = ?',
                [$newProductId]
            )->fetchFirstColumn()
        );
        // pim_catalog_product_unique_data
        Assert::assertSame(
            [$newProductUuid],
            $this->connection->executeQuery(
                'SELECT DISTINCT BIN_TO_UUID(product_uuid) FROM pim_catalog_product_unique_data WHERE product_id = ?',
                [$newProductId]
            )->fetchFirstColumn()
        );
        // pim_versioning_version
        Assert::assertSame(
            [$newProductUuid],
            $this->connection->executeQuery(
                'SELECT DISTINCT BIN_TO_UUID(resource_uuid) FROM pim_versioning_version WHERE resource_id = ? AND resource_name="Akeneo\\\Pim\\\Enrichment\\\Component\\\Product\\\Model\\\Product"',
                [$newProductId]
            )->fetchFirstColumn()
        );
        // pim_data_quality_insights_product_criteria_evaluation
        Assert::assertSame(
            [$newProductUuid],
            $this->connection->executeQuery(
                'SELECT DISTINCT BIN_TO_UUID(product_uuid) FROM pim_data_quality_insights_product_criteria_evaluation WHERE product_id = ?',
                [$newProductId]
            )->fetchFirstColumn()
        );
        // pim_data_quality_insights_product_score
        ($this->get(EvaluateProducts::class))(ProductUuidCollection::fromInts([$newProductId]));
        Assert::assertSame(
            [$newProductUuid],
            $this->connection->executeQuery(
                'SELECT DISTINCT BIN_TO_UUID(product_uuid) FROM pim_data_quality_insights_product_score WHERE product_id = ?',
                [$newProductId]
            )->fetchFirstColumn()
        );

        /**
         * Update product - Please note that it does not necessarily test the update of foreign table rows, because,
         * even if we update the product, most of the actions on other tables still are INSERT and DELETE, not UPDATE.
         */
        $this->get('pim_catalog.updater.product')->update($product, [
            'categories' => ['master', 'categoryA'],
            'associations' => ['X_SELL' => ['products' => ['identifier1']]],
            'groups' => ['groupA', 'groupB'],
        ]);
        $this->get('pim_catalog.validator.product')->validate($product);
        $this->get('pim_catalog.saver.product')->save($product);
        Assert::assertSame($newProductUuid, $this->getProductUuid('new_product'));

        // pim_catalog_association
        $ownerdUuids = $this->connection->executeQuery(
            'SELECT DISTINCT BIN_TO_UUID(owner_uuid) FROM pim_catalog_association'
        )->fetchFirstColumn();
        Assert::assertSame([$this->getProductUuid('new_product')], $ownerdUuids);
        // pim_catalog_association_product
        Assert::assertTrue(
            (bool)$this->connection->executeQuery(
                'SELECT EXISTS (SELECT 1 FROM pim_catalog_association_product WHERE product_uuid = UUID_TO_BIN(?)) AS e',
                [$this->getProductUuid('identifier1')]
            )->fetchOne()
        );
        // pim_catalog_category_product
        Assert::assertSame(
            [$newProductUuid],
            $this->connection->executeQuery(
                'SELECT DISTINCT BIN_TO_UUID(product_uuid) FROM pim_catalog_category_product WHERE product_id = ?',
                [$newProductId]
            )->fetchFirstColumn()
        );
        // pim_catalog_group_product
        Assert::assertSame(
            [$newProductUuid],
            $this->connection->executeQuery(
                'SELECT DISTINCT BIN_TO_UUID(product_uuid) FROM pim_catalog_group_product WHERE product_id = ?',
                [$newProductId]
            )->fetchFirstColumn()
        );
        // pim_catalog_product_unique_data
        Assert::assertSame(
            [$newProductUuid],
            $this->connection->executeQuery(
                'SELECT DISTINCT BIN_TO_UUID(product_uuid) FROM pim_catalog_product_unique_data WHERE product_id = ?',
                [$newProductId]
            )->fetchFirstColumn()
        );
        // pim_versioning_version
        Assert::assertSame(
            [$newProductUuid],
            $this->connection->executeQuery(
                'SELECT DISTINCT BIN_TO_UUID(resource_uuid) FROM pim_versioning_version WHERE resource_id = ? AND resource_name="Akeneo\\\Pim\\\Enrichment\\\Component\\\Product\\\Model\\\Product"',
                [$newProductId]
            )->fetchFirstColumn()
        );
        // pim_data_quality_insights_product_criteria_evaluation
        Assert::assertSame(
            [$newProductUuid],
            $this->connection->executeQuery(
                'SELECT DISTINCT BIN_TO_UUID(product_uuid) FROM pim_data_quality_insights_product_criteria_evaluation WHERE product_id = ?',
                [$newProductId]
            )->fetchFirstColumn()
        );
        // pim_data_quality_insights_product_score
        ($this->get(EvaluateProducts::class))(ProductUuidCollection::fromInt($newProductId));
        Assert::assertSame(
            [$newProductUuid],
            $this->connection->executeQuery(
                'SELECT DISTINCT BIN_TO_UUID(product_uuid) FROM pim_data_quality_insights_product_score WHERE product_id = ?',
                [$newProductId]
            )->fetchFirstColumn()
        );

        /**
         * Create product model
         */
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update($productModel, [
            'code' => 'test_pm',
            'family_variant' => 'familyAVariant',
            'associations' => [
                'X_SELL' => ['products' => ['identifier2']],
            ],
        ]);
        $violations = $this->get('pim_catalog.validator.product_model')->validate($productModel);
        Assert::assertCount(0, $violations, \sprintf('The product model is invalid: %s', (string)$violations));
        $this->get('pim_catalog.saver.product_model')->save($productModel);
        // pim_catalog_association_product_model_to_product
        Assert::assertSame(
            [$this->getProductUuid('identifier2')],
            $this->connection->executeQuery(
                'SELECT DISTINCT BIN_TO_UUID(product_uuid) FROM pim_catalog_association_product_model_to_product'
            )->fetchFirstColumn()
        );

        // pim_comment_comment
        $comment = $this->createComment($product);
        Assert::assertSame(
            $this->getProductUuid('new_product'),
            $this->connection->executeQuery(
                'SELECT BIN_TO_UUID(resource_uuid) FROM pim_comment_comment WHERE id = ?',
                [$comment->getId()]
            )->fetchOne()
        );
    }

    private function assertProductsAreReindexed(): void
    {
        $indexedProducts = $this->getIndexedProducts();
        Assert::assertNotContains('identifier_removed', $indexedProducts);

        foreach ($indexedProducts as $esId => $identifier) {
            $split = \preg_match('/^product_(?P<uuid>.*)$/', $esId, $matches);
            Assert::assertSame(1, $split);
            Assert::assertTrue(Uuid::isValid($matches['uuid']));
            Assert::assertTrue(
                (bool)$this->connection->executeQuery(
                    'SELECT EXISTS(SELECT * FROM pim_catalog_product WHERE identifier = :identifier AND BIN_TO_UUID(uuid) = :uuid)',
                    ['identifier' => $identifier, 'uuid' => $matches['uuid']]
                )
            );
        }
    }

    private function assertColumnsAreNullable(): void
    {
        $tableWithNullableColumnsList = [
            'pim_versioning_version' => ['resource_id'],
        ];

        foreach ($tableWithNullableColumnsList as $tableName => $columns) {
            foreach ($columns as $columnName) {
                Assert::assertTrue($this->isColumnNullable($tableName, $columnName));
            }
        }
    }

    private function getIndexedProducts(): array
    {
        /** @var Client $esClient */
        $esClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
        $esClient->refreshIndex();
        $result = $esClient->search([
            'query' => [
                'term' => [
                    'document_type' => ProductInterface::class,
                ],
            ],
            'fields' => ['id', 'identifier'],
            '_source' => false,
            'size' => 100,
        ]);

        $esProducts = [];
        foreach ($result['hits']['hits'] as $document) {
            $identifier = $document['fields']['identifier'][0];
            $esProducts[$document['_id']] = $identifier;
        }

        return $esProducts;
    }

    private function isColumnNullable(string $tableName, string $columnName): bool {
        $schema = $this->connection->getDatabase();
        $sql = <<<SQL
            SELECT IS_NULLABLE
            FROM information_schema.columns
            WHERE table_schema=:schema
              AND table_name=:tableName
              AND column_name=:columnName;
        SQL;

        $result = $this->connection->fetchOne($sql, [
            'schema' => $schema,
            'tableName' => $tableName,
            'columnName' => $columnName
        ]);

        return $result !== 'NO';
    }

    private function loadFixtures(): void
    {
        $this->adminUser = $this->createAdminUser();

        $this->createQuantifiedAssociationType('SOIREEFOOD10');

        foreach (range(1, 10) as $i) {
            $this->get('pim_enrich.product.message_bus')->dispatch(
                new UpsertProductCommand(
                    userId: $this->adminUser->getId(),
                    productIdentifier: 'identifier' . $i
                )
            );

            $this->connection->executeQuery(
                \strtr(
                    <<<SQL
                UPDATE pim_catalog_product
                SET quantified_associations = '{"SOIREEFOOD10":{"products":[{"id":{associated_product_id},"quantity":1000}]}}'
                WHERE id = {product_id}
            SQL,
                    [
                        '{product_id}' => $i,
                        '{associated_product_id}' => $i - 1,
                    ]
                )
            );
        }

        // test product only in ES index
        $this->get('pim_enrich.product.message_bus')->dispatch(
            new UpsertProductCommand(
                userId: $this->adminUser->getId(),
                productIdentifier: 'identifier_removed',
            )
        );

        $this->createProductGroup(['code' => 'groupA', 'type' => 'RELATED']);
        $this->createProductGroup(['code' => 'groupB', 'type' => 'RELATED']);
        $this->createCategory(['code' => 'categoryA']);
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'group' => 'other',
        ]);
        $this->createAttribute([
            'code' => 'axe_attr',
            'type' => 'pim_catalog_boolean',
            'group' => 'other',
        ]);
        $this->createFamily([
            'code' => 'familyA',
            'attributes' => ['sku', 'name', 'axe_attr'],
        ]);
        $this->createFamilyVariant([
            'code' => 'familyAVariant',
            'family' => 'familyA',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'attributes' => ['name'],
                    'axes' => ['axe_attr'],
                ],
            ],
        ]);

        // reindex products with their mysql ids
        $productIndexer = $this->get('pim_catalog.elasticsearch.indexer.product');
        $productIndexer->removeFromProductUuids(
            \array_map(
                static fn (string $uuid): UuidInterface => Uuid::fromString($uuid),
                $this->connection->executeQuery('SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product')->fetchFirstColumn()
            )
        );
        $this->connection->executeQuery('UPDATE pim_catalog_product SET uuid = NULL');
        $productIndexer->indexFromProductIdentifiers(
            $this->connection->executeQuery('SELECT identifier FROM pim_catalog_product')->fetchFirstColumn()
        );
        $this->connection->executeQuery('DELETE FROM pim_catalog_product WHERE identifier = "identifier_removed"');
        Assert::assertContains('identifier_removed', $this->getIndexedProducts());
    }

    private function createComment(ProductInterface $product): Comment
    {
        $comment = new Comment();
        $comment->setAuthor($this->adminUser);
        $comment->setCreatedAt(new \DateTime());
        $comment->setRepliedAt(new \DateTime());
        $comment->setBody('pouet');
        $comment->setResourceName(ClassUtils::getClass($product));
        $comment->setResourceId($product->getId());
        $this->getContainer()->get('pim_comment.saver.comment')->save($comment);

        return $comment;
    }
}
