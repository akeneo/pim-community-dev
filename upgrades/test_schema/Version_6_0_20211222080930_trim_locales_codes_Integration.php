<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class Version_6_0_20211222080930_trim_locales_codes_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20211222080930_trim_locales_codes';

    public function test_it_trims_locale_codes(): void
    {
        $this->addLocaleWithExtraSpaces();

        $this->assertTrue($this->localeTableNeedsTrim());
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertFalse($this->localeTableNeedsTrim());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function addLocaleWithExtraSpaces(): void
    {
        $this->getConnection()->executeStatement(<<<SQL
            INSERT INTO pim_catalog_locale (code, is_activated)
            VALUES (' global_fr', 1);
        SQL);

        $this->getConnection()->executeStatement(<<<SQL
            INSERT INTO pim_catalog_locale (code, is_activated)
            VALUES ('global_en ', 1);
        SQL);
    }

    private function localeTableNeedsTrim(): bool
    {
        $query = <<<SQL
SELECT *
FROM pim_catalog_locale
WHERE code LIKE ' %' OR code LIKE '% '
SQL;

        return !empty($this->getConnection()->fetchAllAssociative($query));
    }
}
