<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\ORM\Locale;

use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Locale\GetAllActiveLocalesCodes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class GetAllActiveLocalesCodesIntegration extends TestCase
{
    private GetAllActiveLocalesCodes $query;

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
    }

    public function testItFetchesAllLocales(): void
    {
        $expected = ['en_US', 'fr_FR', 'de_DE', 'ru_RU'];

        $this->activateLocales($expected);

        $results = $this->query->execute();

        $this->assertEqualsCanonicalizing($expected, $results, 'Locales codes are not matched');
    }

    private function activateLocales(array $localeCodes): void
    {
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier('ecommerce');
        $localeRepository = $this->get('pim_catalog.repository.locale');
        $localeSaver = $this->get('pim_catalog.saver.locale');

        foreach ($localeCodes as $localeCode) {
            $locale = $localeRepository->findOneByIdentifier($localeCode);
            $locale->addChannel($channel);

            $errors = $this->get('validator')->validate($locale);
            Assert::assertCount(0, $errors);
            $localeSaver->save($locale);
        }
    }
}
