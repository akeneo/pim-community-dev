<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230425090000_remove_duplicated_variant_product_Integration extends TestCase
{
    private const MIGRATION_NAME = '_8_0_20230425090000_remove_duplicated_variant_product';

    use ExecuteMigrationTrait;

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_removes_duplicated_variant_product(): void
    {
        $this->givenProductModelAndVariantProductsDuplicated();

        Assert::assertTrue($this->haveDuplicatedVariantProduct(), 'DB should contain variant product duplicated');
        $this->reExecuteMigration(self::MIGRATION_NAME);
        Assert::assertFalse($this->haveDuplicatedVariantProduct(), 'DB should not contain variant product duplicated');
    }

    public function test_it_does_nothing_if_no_variant_product_is_duplicated(): void
    {
        Assert::assertFalse($this->haveDuplicatedVariantProduct(), 'DB should not contain variant product duplicated');
        $this->reExecuteMigration(self::MIGRATION_NAME);
        Assert::assertFalse($this->haveDuplicatedVariantProduct(), 'DB should not contain variant product duplicated');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function haveDuplicatedVariantProduct(): bool
    {
        $sql = <<<SQL
SELECT   COUNT(*) AS duplicate_nb, product_model_id, raw_values
FROM     pim_catalog_product
GROUP BY product_model_id, raw_values
HAVING   COUNT(*) > 1
SQL;

        $result = $this->connection->fetchAllAssociative($sql);

        return \count($result) > 0;
    }

    private function givenProductModelAndVariantProductsDuplicated(): void
    {
        $insertFamily = <<<SQL
INSERT IGNORE INTO pim_catalog_family (code, created, updated)
VALUES ('my_shirts_family', NOW(), NOW());
SQL;
        $this->connection->executeQuery($insertFamily);
        $familyId = $this->connection->lastInsertId();

        $insertFamilyVariant = <<<SQL
INSERT IGNORE INTO pim_catalog_family_variant (family_id, code)
VALUES (:family_id, 'my_shirts_family_size');
SQL;
        $this->connection->executeQuery($insertFamilyVariant, ['family_id' => $familyId]);
        $familyVariantId = $this->connection->lastInsertId();

        $insertProductModel = <<<SQL
INSERT IGNORE INTO pim_catalog_product_model (parent_id, family_variant_id, code, raw_values, created, updated)
VALUES (null, :family_variant_id, 'my_shirt', '{}', NOW(), NOW());
SQL;
        $this->connection->executeQuery($insertProductModel, ['family_variant_id' => $familyVariantId]);
        $productModelId = $this->connection->lastInsertId();

        $insertProducts = <<<SQL
INSERT IGNORE INTO pim_catalog_product (uuid, family_id, product_model_id, family_variant_id, is_enabled, identifier, raw_values, created, updated) VALUES
    (UUID_TO_BIN(:uuid1), :family_id, :product_model_id, :family_variant_id, 1, null, '{"size": {"<all_channels>": {"<all_locales>": "s"}}}',  NOW(), NOW()),
    (UUID_TO_BIN(:uuid2), :family_id, :product_model_id, :family_variant_id, 1, null, '{"size": {"<all_channels>": {"<all_locales>": "s"}}}',  NOW(), NOW())
SQL;

        $this->connection->executeQuery($insertProducts, [
            'uuid1' => Uuid::uuid4()->toString(),
            'uuid2' => Uuid::uuid4()->toString(),
            'family_id' => $familyId,
            'product_model_id' => $productModelId,
            'family_variant_id' => $familyVariantId
        ]);
    }
}
