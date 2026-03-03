<?php

namespace Akeneo\Test\Pim\Structure\Family\Test\Integration\Query\Sql;

use Akeneo\Pim\Structure\Family\Infrastructure\Query\Sql\SqlFindFamiliesWithLabels;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyQuery;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyQueryPagination;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyQuerySearch;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FamilyWithLabels;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\FindFamiliesWithLabels;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

class SqlFindFamiliesWithLabelsIntegration extends TestCase
{
    private SqlFindFamiliesWithLabels $sqlFindFamiliesWithLabels;

    public function setUp(): void
    {
        parent::setUp();
        $this->sqlFindFamiliesWithLabels = $this->get(FindFamiliesWithLabels::class);

        $this->createFamily('beers', ['fr_FR' => 'Bières']);
        $this->createFamily('bikes', ['fr_FR' => 'Vélos', 'en_US' => 'Bikes']);
        $this->createFamily('screens', ['fr_FR' => 'Écrans', 'en_US' => 'Screens']);
        $this->createFamily('tvs', []);
        $this->createFamily('123', ['en_US' => 'With code number']);
    }

    public function test_it_returns_code_and_labels_of_all_families(): void
    {
        $query = new FamilyQuery();

        $expectedFamilies = [
            new FamilyWithLabels('beers', ['fr_FR' => 'Bières']),
            new FamilyWithLabels('bikes', ['fr_FR' => 'Vélos', 'en_US' => 'Bikes']),
            new FamilyWithLabels('screens', ['fr_FR' => 'Écrans', 'en_US' => 'Screens']),
            new FamilyWithLabels('tvs', []),
            new FamilyWithLabels('123', ['en_US' => 'With code number']),
        ];

        $actualFamilies = $this->sqlFindFamiliesWithLabels->fromQuery($query);

        self::assertEqualsCanonicalizing($expectedFamilies, $actualFamilies);
    }

    public function test_it_returns_code_and_labels_with_code_is_a_number(): void
    {
        $query = new FamilyQuery(
            search: new FamilyQuerySearch(
                value: 'code number',
            ),
        );

        $expectedFamilies = [
            new FamilyWithLabels('123', ['en_US' => 'With code number']),
        ];

        $actualFamilies = $this->sqlFindFamiliesWithLabels->fromQuery($query);

        self::assertEqualsCanonicalizing($expectedFamilies, $actualFamilies);
    }

    public function test_it_returns_code_and_labels_of_all_families_with_search_language_but_no_search(): void
    {
        $query = new FamilyQuery(
            search: new FamilyQuerySearch(
                labelLocale: 'fr_FR',
            ),
        );

        $expectedFamilies = [
            new FamilyWithLabels('beers', ['fr_FR' => 'Bières']),
            new FamilyWithLabels('bikes', ['fr_FR' => 'Vélos', 'en_US' => 'Bikes']),
            new FamilyWithLabels('screens', ['fr_FR' => 'Écrans', 'en_US' => 'Screens']),
            new FamilyWithLabels('tvs', []),
            new FamilyWithLabels('123', ['en_US' => 'With code number']),
        ];

        $actualFamilies = $this->sqlFindFamiliesWithLabels->fromQuery($query);

        self::assertEqualsCanonicalizing($expectedFamilies, $actualFamilies);
    }

    public function test_it_returns_code_and_labels_of_filtered_families_by_search(): void
    {
        $query = new FamilyQuery(
            search: new FamilyQuerySearch(
                value: 'Bi',
            ),
        );

        $expectedFamilies = [
            new FamilyWithLabels('beers', ['fr_FR' => 'Bières']),
            new FamilyWithLabels('bikes', ['fr_FR' => 'Vélos', 'en_US' => 'Bikes']),
        ];

        $actualFamilies = $this->sqlFindFamiliesWithLabels->fromQuery($query);

        self::assertEqualsCanonicalizing($expectedFamilies, $actualFamilies);
    }

    public function test_it_returns_code_and_labels_of_filtered_families_by_search_and_search_language(): void
    {
        $query = new FamilyQuery(
            search: new FamilyQuerySearch(
                value: 'Bi',
                labelLocale: 'en_US',
            ),
        );

        $expectedFamilies = [
            new FamilyWithLabels('bikes', ['fr_FR' => 'Vélos', 'en_US' => 'Bikes']),
        ];

        $actualFamilies = $this->sqlFindFamiliesWithLabels->fromQuery($query);

        self::assertEqualsCanonicalizing($expectedFamilies, $actualFamilies);
    }

    public function test_it_returns_code_and_labels_of_filtered_families_among_an_include_codes_list(): void
    {
        $query = new FamilyQuery(
            search: new FamilyQuerySearch(
                value: 't',
            ),
            includeCodes: ['bikes', 'tvs'],
        );

        $expectedFamilies = [
            new FamilyWithLabels('tvs', []),
        ];

        $actualFamilies = $this->sqlFindFamiliesWithLabels->fromQuery($query);

        self::assertEqualsCanonicalizing($expectedFamilies, $actualFamilies);
    }

    public function test_it_returns_code_and_labels_of_filtered_families_among_an_empty_include_codes_list(): void
    {
        $query = new FamilyQuery(
            search: new FamilyQuerySearch(
                value: 'Scr',
            ),
            includeCodes: [],
        );

        $expectedFamilies = [];

        $actualFamilies = $this->sqlFindFamiliesWithLabels->fromQuery($query);

        self::assertEqualsCanonicalizing($expectedFamilies, $actualFamilies);
    }

    public function test_it_returns_code_and_labels_of_filtered_families_with_an_empty_exclude_codes_list(): void
    {
        $query = new FamilyQuery(
            search: new FamilyQuerySearch(
                value: 'Scr',
                labelLocale: 'en_US',
            ),
        );

        $expectedFamilies = [
            new FamilyWithLabels('screens', ['fr_FR' => 'Écrans', 'en_US' => 'Screens']),
        ];

        $actualFamilies = $this->sqlFindFamiliesWithLabels->fromQuery($query);

        self::assertEqualsCanonicalizing($expectedFamilies, $actualFamilies);
    }

    public function test_it_returns_code_and_labels_of_filtered_families_with_exclude_codes(): void
    {
        $query = new FamilyQuery(
            search: new FamilyQuerySearch(
                value: 'b',
            ),
            excludeCodes: ['beers', '123'],
        );

        $expectedFamilies = [
            new FamilyWithLabels('bikes', ['fr_FR' => 'Vélos', 'en_US' => 'Bikes']),
        ];

        $actualFamilies = $this->sqlFindFamiliesWithLabels->fromQuery($query);

        self::assertEqualsCanonicalizing($expectedFamilies, $actualFamilies);
    }

    public function test_it_returns_code_and_labels_of_filtered_families_with_limit_and_page(): void
    {
        $query = new FamilyQuery(
            pagination: new FamilyQueryPagination(
                page: 3,
                limit: 1,
            )
        );

        $expectedFamilies = [
            new FamilyWithLabels('bikes', ['fr_FR' => 'Vélos', 'en_US' => 'Bikes']),
        ];

        $actualFamilies = $this->sqlFindFamiliesWithLabels->fromQuery($query);

        self::assertEqualsCanonicalizing($expectedFamilies, $actualFamilies);
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
