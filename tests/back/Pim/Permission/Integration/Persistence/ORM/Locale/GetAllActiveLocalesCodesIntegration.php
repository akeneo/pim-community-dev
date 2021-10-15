<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\ORM\Locale;

use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale\GetAllActiveLocalesCodes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use AkeneoTestEnterprise\Pim\Permission\FixturesLoader\LocaleFixturesLoader;
use PHPUnit\Framework\Assert;

class GetAllActiveLocalesCodesIntegration extends TestCase
{
    private GetAllActiveLocalesCodes $query;
    private LocaleFixturesLoader $localeFixturesLoader;

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

        $this->query = $this->get(GetAllActiveLocalesCodes::class);
        $this->localeFixturesLoader = $this->get('akeneo_integration_tests.loader.locale');
    }

    public function testItFetchesAllLocales(): void
    {
        $expected = ['en_US', 'fr_FR', 'de_DE', 'ru_RU'];

        $this->localeFixturesLoader->activateLocalesOnChannel($expected, 'ecommerce');

        $results = $this->query->execute();

        $this->assertEqualsCanonicalizing($expected, $results, 'Locales codes are not matched');
    }
}
