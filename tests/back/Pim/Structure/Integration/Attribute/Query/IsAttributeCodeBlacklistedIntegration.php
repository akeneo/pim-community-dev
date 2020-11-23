<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Family;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class IsAttributeCodeBlacklistedIntegration extends TestCase
{
    public function test_it_check_if_existing_attribute_code_is_blacklisted(): void
    {
        $query = $this->get('akeneo.pim.structure.query.is_attribute_code_blacklisted');
        $result = $query->execute('description');

        $this->assertFalse($result);
    }

    public function test_it_check_if_blacklisted_attribute_code_is_blacklisted(): void
    {
        $query = $this->get('akeneo.pim.structure.query.is_attribute_code_blacklisted');

        $blacklistAttributeCode = <<<SQL
        INSERT INTO `pim_catalog_attribute_blacklist` (`attribute_code`)
        VALUES
            ('description');
        SQL;

        /** @var Connection $connection */
        $connection = $this->get('database_connection');
        $connection->executeUpdate($blacklistAttributeCode);

        $result = $query->execute('description');

        $this->assertTrue($result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
