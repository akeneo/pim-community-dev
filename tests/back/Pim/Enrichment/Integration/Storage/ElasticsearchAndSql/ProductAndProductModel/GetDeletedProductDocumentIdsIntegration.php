<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel\GetDeletedProductDocumentIds;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Helper\AuthenticatorHelper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetDeletedProductDocumentIdsIntegration extends TestCase
{
    /** @test */
    public function it_returns_nothing_if_product_was_deleted(): void
    {
        $this->assertDeletedProductDocuments([]);
    }

    /** @test */
    public function it_fetches_deleted_product_document_ids(): void
    {
        $deletedIds = $this->deleteProductsFromDb(10);
        $this->assertDeletedProductDocuments(
            \array_map(
                static fn (string $uuid): string => \sprintf('product_%s', $uuid),
                $deletedIds
            )
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->getAuthenticator()->logIn('admin');
        $adminId = $this->getAdminId();

        for ($i = 0; $i < 30; $i++) {
            $this->get('pim_enrich.product.message_bus')->dispatch(
                UpsertProductCommand::createWithoutUuidNorIdentifier($adminId, [])
            );
        }
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function assertDeletedProductDocuments(array $expectedIds): void
    {
        $getDeletedProductDocumentIds = $this->get(GetDeletedProductDocumentIds::class);

        Assert::assertEqualsCanonicalizing($expectedIds, \iterator_to_array($getDeletedProductDocumentIds()));
    }

    private function deleteProductsFromDb(int $count): array
    {
        $randomUuids = $this->getConnection()->fetchFirstColumn(
            'SELECT BIN_TO_UUID(uuid) AS uuid FROM pim_catalog_product ORDER BY RAND() LIMIT :limit',
            ['limit' => $count],
            ['limit' => ParameterType::INTEGER]
        );

        $this->getConnection()->executeStatement(
            'DELETE FROM pim_catalog_product WHERE uuid IN (:uuids)',
            [
                'uuids' => \array_map(
                    static fn (string $uuid): string => Uuid::fromString($uuid)->getBytes(),
                    $randomUuids
                ),
            ],
            ['uuids' => Connection::PARAM_STR_ARRAY]
        );

        return $randomUuids;
    }

    private function getAdminId(): int
    {
        return (int)$this->getConnection()->fetchOne(
            'SELECT id FROM oro_user WHERE username = :username',
            ['username' => 'admin'],
        );
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function getAuthenticator(): AuthenticatorHelper
    {
        return $this->get('akeneo_integration_tests.helper.authenticator');
    }
}
