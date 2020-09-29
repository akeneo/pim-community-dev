<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Family;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Family\Sql\SqlGetFamilyTranslations;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

final class SqlGetFamilyTranslationsIntegration extends TestCase
{
    public function test_it_gets_family_translations_by_giving_family_codes_and_locale_code(): void
    {
        $query = $this->getQuery();

        $this->givenFamilies([
            [
                'code' => 'new_shoes',
                'labels' => [
                    'en_US' => 'new shoes',
                    'fr_FR' => 'nouvelles chaussures'
                ]
            ],
            [
                'code' => 'new_accessories',
                'labels' => [
                    'en_US' => 'new accessories',
                    'fr_FR' => 'nouveaux accessoires'
                ]
            ]
        ]);

        $expected = [
            'new_accessories' => 'nouveaux accessoires',
            'new_shoes' => 'nouvelles chaussures',
        ];
        $actual = $query->byFamilyCodesAndLocale(['new_shoes', 'new_accessories'], 'fr_FR');

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getQuery(): SqlGetFamilyTranslations
    {
        return $this->get('akeneo.pim.structure.query.get_family_translations');
    }

    private function givenFamilies(array $families): void
    {
        $families = array_map(function (array $familyData) {
            $family = $this->get('pim_catalog.factory.family')->create();
            $this->get('pim_catalog.updater.family')->update($family, $familyData);
            $constraintViolations = $this->get('validator')->validate($family);

            Assert::count($constraintViolations, 0);

            return $family;
        }, $families);

        $this->get('pim_catalog.saver.family')->saveAll($families);
    }
}
