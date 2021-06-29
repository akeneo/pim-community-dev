<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Category;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Category\Sql\SqlGetCategoryTranslations;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

final class SqlGetCategoryTranslationsIntegration extends TestCase
{
    public function test_it_gets_category_translations_by_giving_category_codes_and_locale_code(): void
    {
        $query = $this->getQuery();

        $this->givenCategories([
            [
                'code' => 'new_furniture',
                'labels' => [
                    'en_US' => 'new furniture',
                    'fr_FR' => 'nouveaux meubles'
                ]
            ],
            [
                'code' => 'new_clothes',
                'labels' => [
                    'en_US' => 'new clothes',
                    'fr_FR' => 'nouveaux habits'
                ]
            ]
        ]);

        $expected = [
            'new_clothes' => 'nouveaux habits',
            'new_furniture' => 'nouveaux meubles',
        ];
        $actual = $query->byCategoryCodesAndLocale(['new_furniture', 'new_clothes'], 'fr_FR');

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getQuery(): SqlGetCategoryTranslations
    {
        return $this->get('akeneo.pim.structure.query.get_category_translations');
    }

    private function givenCategories(array $categories): void
    {
        $categories = array_map(function (array $categoryData) {
            $category = $this->get('pim_catalog.factory.category')->create();
            $this->get('pim_catalog.updater.category')->update($category, $categoryData);
            $constraintViolations = $this->get('validator')->validate($category);

            Assert::count($constraintViolations, 0);

            return $category;
        }, $categories);

        $this->get('pim_catalog.saver.category')->saveAll($categories);
    }
}
