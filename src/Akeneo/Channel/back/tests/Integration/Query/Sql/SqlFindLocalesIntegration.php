<?php

namespace Akeneo\Test\Channel\Integration\Query\Sql;

use Akeneo\Channel\API\Query\FindLocales;
use Akeneo\Channel\API\Query\Locale;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class SqlFindLocalesIntegration extends TestCase
{
    private FindLocales $sqlFindLocales;

    public function setUp(): void
    {
        parent::setUp();

        $this->sqlFindLocales = $this->get(
            'Akeneo\Channel\Infrastructure\Query\Sql\SqlFindLocales'
        );
    }

    public function test_it_finds_a_locale_by_its_code(): void
    {
        $activeLocale = $this->sqlFindLocales->find('en_US');
        $this->assertInstanceOf(Locale::class, $activeLocale);
        $this->assertEquals('en_US', $activeLocale->getCode());
        $this->assertEquals(true, $activeLocale->isActivated());

        $unactiveLocale = $this->sqlFindLocales->find('uk_UA');
        $this->assertInstanceOf(Locale::class, $unactiveLocale);
        $this->assertEquals('uk_UA', $unactiveLocale->getCode());
        $this->assertEquals(false, $unactiveLocale->isActivated());

        $unknownLocale = $this->sqlFindLocales->find('unknown');
        $this->assertNull($unknownLocale);
    }

    public function test_it_finds_all_activated_locales(): void
    {
        $results = $this->sqlFindLocales->findAllActivated();

        $this->assertIsArray($results);
        $this->assertCount(3, $results);
        $this->assertContainsOnlyInstancesOf(Locale::class, $results);

        foreach ($results as $result) {
            $this->assertEquals(true, $result->isActivated());
        }

        $enUSLocale = current(array_filter($results, fn (Locale $channel) => $channel->getCode() === 'en_US'));

        $this->assertEquals('en_US', $enUSLocale->getCode());
        $this->assertEquals(true, $enUSLocale->isActivated());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
