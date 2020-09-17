<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\FamilyVariant;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\FamilyVariant\Sql\SqlGetFamilyVariantTranslations;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

final class SqlGetFamilyVariantTranslationsIntegration extends TestCase
{
    public function test_it_gets_family_variant_translations_by_giving_family_variant_codes_and_locale_code(): void
    {
        $query = $this->getQuery();

        $this->givenFamiliesVariant([
            [
                'code' => 'new_shoes_color',
                'labels' => [
                    'en_US' => 'new shoes color',
                    'fr_FR' => 'nouvelles chaussures couleur'
                ],
                'family' => 'familyA',
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'axes' => ['a_simple_select'],
                        'attributes' => ['a_simple_select'],
                    ]
                ]
            ],
            [
                'code' => 'new_accessories_small',
                'labels' => [
                    'en_US' => 'new accessories small',
                    'fr_FR' => 'nouveaux accessoires petits'
                ],
                'family' => 'familyA',
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'axes' => ['a_simple_select'],
                        'attributes' => ['a_simple_select'],
                    ]
                ]
            ]
        ]);

        $expected = [
            'new_accessories_small' => 'nouveaux accessoires petits',
            'new_shoes_color' => 'nouvelles chaussures couleur',
        ];
        $actual = $query->byFamilyVariantCodesAndLocale(['new_shoes_color', 'new_accessories_small'], 'fr_FR');

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getQuery(): SqlGetFamilyVariantTranslations
    {
        return $this->get('akeneo.pim.structure.query.get_family_variant_translations');
    }

    private function givenFamiliesVariant(array $familiesVariant): void
    {
        $familiesVariant = array_map(function (array $familyVariantData) {
            $familyVariant = $this->get('pim_catalog.factory.family_variant')->create();
            $this->get('pim_catalog.updater.family_variant')->update($familyVariant, $familyVariantData);
            $constraintViolations = $this->get('validator')->validate($familyVariant);

            Assert::count($constraintViolations, 0);

            return $familyVariant;
        }, $familiesVariant);

        $this->get('pim_catalog.saver.family_variant')->saveAll($familiesVariant);
    }
}
