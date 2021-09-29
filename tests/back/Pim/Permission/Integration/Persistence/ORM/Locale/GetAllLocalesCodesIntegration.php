<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\ORM\Locale;

use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale\GetAllLocalesCodes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class GetAllLocalesCodesIntegration extends TestCase
{
    private GetAllLocalesCodes $query;

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(GetAllLocalesCodes::class);
    }

    public function testItFetchesAllLocales(): void
    {
        $expected = $this->getAllFixturesLocales();

        $results = $this->query->execute();

        $this->assertEqualsCanonicalizing($expected, $results, 'Locales codes are not matched');
    }

    private function getAllFixturesLocales(): array
    {
        $connection =  $this->get('database_connection');

        $query = 'SELECT code FROM pim_catalog_locale';

        $results = $connection->fetchAll($query) ?: [];

        return array_map(fn ($row) => $row['code'], $results);
    }
}
