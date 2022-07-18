<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Family;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Family\Sql\SqlSearchFamilies;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\SearchFamiliesParameters;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

final class SqlSearchFamiliesIntegration extends TestCase
{
    private SqlSearchFamilies $sqlSearchFamilies;

    public function setUp(): void
    {
        parent::setUp();
        $this->sqlSearchFamilies = $this->get('akeneo.pim.structure.query.search_families');

        $this->createFamily('beers', ['fr_FR' => 'Bières']);
        $this->createFamily('bikes', ['fr_FR' => 'Vélos', 'en_US' => 'Bikes']);
        $this->createFamily('tvs', []);
        $this->createFamily('screens', ['fr_FR' => 'Écrans', 'en_US' => 'Screens']);
    }

    public function test_it_should_return_all_families_without_or_without_locales(): void
    {
        $searchParameters = new SearchFamiliesParameters();
        $searchResult = $this->sqlSearchFamilies->search($searchParameters);

        self::assertEquals([
            'matches_count' => 4,
            'items' => [
                [
                    'code' => 'beers',
                    'labels' => ['fr_FR' => 'Bières'],
                ],
                [
                    'code' => 'bikes',
                    'labels' => ['fr_FR' => 'Vélos', 'en_US' => 'Bikes'],
                ],
                [
                    'code' => 'screens',
                    'labels' => ['fr_FR' => 'Écrans', 'en_US' => 'Screens'],
                ],
                [
                    'code' => 'tvs',
                    'labels' => [],
                ],
            ],
        ], $searchResult->normalize());
    }

    public function test_it_should_return_all_families_with_locale(): void
    {
        $searchParameters = new SearchFamiliesParameters();
        $searchParameters->setSearchLanguage('en_US');
        $searchResult = $this->sqlSearchFamilies->search($searchParameters);

        self::assertEquals([
            'matches_count' => 4,
            'items' => [
                [
                    'code' => 'beers',
                    'labels' => ['fr_FR' => 'Bières'],
                ],
                [
                    'code' => 'bikes',
                    'labels' => ['fr_FR' => 'Vélos', 'en_US' => 'Bikes'],
                ],
                [
                    'code' => 'screens',
                    'labels' => ['fr_FR' => 'Écrans', 'en_US' => 'Screens'],
                ],
                [
                    'code' => 'tvs',
                    'labels' => [],
                ],
            ],
        ], $searchResult->normalize());
    }

    public function test_it_searches_families_on_all_locales(): void
    {
        $searchParameters = new SearchFamiliesParameters();
        $searchParameters->setSearch('Bi');
        $searchResult = $this->sqlSearchFamilies->search($searchParameters);

        self::assertEquals([
            'matches_count' => 2,
            'items' => [
                [
                    'code' => 'beers',
                    'labels' => ['fr_FR' => 'Bières'],
                ],
                [
                    'code' => 'bikes',
                    'labels' => ['fr_FR' => 'Vélos', 'en_US' => 'Bikes'],
                ],
            ],
        ], $searchResult->normalize());
    }

    public function test_it_searches_families_on_a_locale(): void
    {
        $searchParameters = new SearchFamiliesParameters();
        $searchParameters->setSearch('Bi');
        $searchParameters->setSearchLanguage('fr_FR');
        $searchResult = $this->sqlSearchFamilies->search($searchParameters);

        self::assertEquals([
            'matches_count' => 2,
            'items' => [
                [
                    'code' => 'beers',
                    'labels' => ['fr_FR' => 'Bières'],
                ],
                [
                    'code' => 'bikes',
                    'labels' => ['fr_FR' => 'Vélos', 'en_US' => 'Bikes'],
                ],
            ],
        ], $searchResult->normalize());
    }

    public function test_it_searches_families_among_an_include_codes_list(): void
    {
        $searchParameters = new SearchFamiliesParameters();
        $searchParameters->setSearch('t');
        $searchParameters->setIncludeCodes(['bikes', 'tvs']);
        $searchResult = $this->sqlSearchFamilies->search($searchParameters);

        self::assertEquals([
            'matches_count' => 1,
            'items' => [
                [
                    'code' => 'tvs',
                    'labels' => [],
                ],
            ],
        ], $searchResult->normalize());
    }

    public function test_it_searches_families_among_an_empty_include_codes_list(): void
    {
        $searchParameters = new SearchFamiliesParameters();
        $searchParameters->setSearch('Scr');
        $searchParameters->setIncludeCodes([]);
        $searchResult = $this->sqlSearchFamilies->search($searchParameters);

        self::assertEquals([
            'matches_count' => 0,
            'items' => [],
        ], $searchResult->normalize());
    }

    public function test_it_searches_families_with_an_empty_exclude_codes_list(): void
    {
        $searchParameters = new SearchFamiliesParameters();
        $searchParameters->setSearch('Scr');
        $searchParameters->setSearchLanguage('en_US');
        $searchParameters->setExcludeCodes([]);
        $searchResult = $this->sqlSearchFamilies->search($searchParameters);

        self::assertEquals([
            'matches_count' => 1,
            'items' => [
                [
                    'code' => 'screens',
                    'labels' => ['fr_FR' => 'Écrans', 'en_US' => 'Screens'],
                ],
            ],
        ], $searchResult->normalize());
    }

    public function test_it_searches_families_and_can_exclude_codes(): void
    {
        $searchParameters = new SearchFamiliesParameters();
        $searchParameters->setSearch('b');
        $searchParameters->setExcludeCodes(['beers']);
        $searchResult = $this->sqlSearchFamilies->search($searchParameters);

        self::assertEquals([
            'matches_count' => 1,
            'items' => [
                [
                    'code' => 'bikes',
                    'labels' => ['fr_FR' => 'Vélos', 'en_US' => 'Bikes'],
                ],
            ],
        ], $searchResult->normalize());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createFamily(string $code, array $labels): void
    {
        $family = $this->get('pim_catalog.factory.family')->create();

        $this->get('pim_catalog.updater.family')->update($family, [
            'code' => $code,
            'labels' => $labels,
        ]);
        $constraints = $this->get('validator')->validate($family);
        Assert::count($constraints, 0);
        $this->get('pim_catalog.saver.family')->save($family);
    }
}
