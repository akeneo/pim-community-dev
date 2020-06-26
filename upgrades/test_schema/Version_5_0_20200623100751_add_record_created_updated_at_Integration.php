<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\Assert;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_5_0_20200623100751_add_record_created_updated_at_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20200623100751_add_record_created_updated_at';

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_every_record_should_have_the_same_created_at_and_updated_at_value()
    {
        $connection = $this->get('database_connection');
        $platform = $connection->getDatabasePlatform();

        $alter = <<<SQL
ALTER TABLE akeneo_reference_entity_record
    DROP COLUMN created_at,
    DROP COLUMN updated_at;
INSERT INTO akeneo_reference_entity_reference_entity
    VALUES ('34', '{"en_US": "michel"}', NULL, NULL, NULL);
INSERT INTO akeneo_reference_entity_record
    VALUES ('1234', 'alphonse', '34', '{}');
SQL;

        $select = <<<SQL
SELECT COUNT(DISTINCT created_at) AS distinct_created_at, COUNT(DISTINCT updated_at) AS distinct_updated_at
FROM akeneo_reference_entity_record;
SQL;

        $connection->executeQuery($alter);
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $result = $connection->executeQuery($select)->fetch(\PDO::FETCH_ASSOC);

        $distinctCreatedAt = Type::getType(Type::INTEGER)->convertToPHPValue($result['distinct_created_at'], $platform);
        $distinctUpdatedAt = Type::getType(Type::INTEGER)->convertToPHPValue($result['distinct_updated_at'], $platform);

        Assert::assertEquals(1, $distinctCreatedAt);
        Assert::assertEquals(1, $distinctUpdatedAt);
    }
}
