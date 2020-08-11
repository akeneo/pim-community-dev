<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Channel;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Channel\Sql\SqlGetChannelTranslations;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class SqlGetChannelTranslationsIntegration extends TestCase
{
    public function test_it_gets_channel_translations_by_giving_locale_code(): void
    {
        $query = $this->getQuery();

        $expected = ['Ecommerce'];
        $actual = $query->byLocale('fr_FR');

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getQuery(): SqlGetChannelTranslations
    {
        return $this->get('akeneo.pim.structure.query.get_channel_translations');
    }
}
