<?php

namespace Akeneo\Test\Pim\Structure\Family\Integration\Query;

use Akeneo\Pim\Structure\Family\API\Model\FamilyWithLabels;
use Akeneo\Pim\Structure\Family\API\Model\FamilyWithLabelsCollection;
use Akeneo\Pim\Structure\Family\API\Query\FamilyQuery;
use Akeneo\Pim\Structure\Family\API\Query\FindFamiliesWithLabels;
use Akeneo\Pim\Structure\Family\Infrastructure\Query\SqlFindFamiliesWithLabels;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

/**
 * @group ce
 */
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
    }

    public function test_it_returns_all_families(): void
    {
        $query = new FamilyQuery();

        $expectedCollection = new FamilyWithLabelsCollection([
            new FamilyWithLabels('beers', ['fr_FR' => 'Bières']),
            new FamilyWithLabels('bikes', ['fr_FR' => 'Vélos', 'en_US' => 'Bikes']),
            new FamilyWithLabels('screens', ['fr_FR' => 'Écrans', 'en_US' => 'Screens']),
            new FamilyWithLabels('tvs', [])
        ]);

        $actualCollection = $this->sqlFindFamiliesWithLabels->fromQuery($query);

        self::assertEquals($expectedCollection, $actualCollection);
    }

    public function test_it_returns_all_families_with_search_language_but_no_search(): void
    {
        $query = new FamilyQuery();
        $query->searchLanguage = 'fr_FR';

        $expectedCollection = new FamilyWithLabelsCollection([
            new FamilyWithLabels('beers', ['fr_FR' => 'Bières']),
            new FamilyWithLabels('bikes', ['fr_FR' => 'Vélos', 'en_US' => 'Bikes']),
            new FamilyWithLabels('screens', ['fr_FR' => 'Écrans', 'en_US' => 'Screens']),
            new FamilyWithLabels('tvs', [])
        ]);

        $actualCollection = $this->sqlFindFamiliesWithLabels->fromQuery($query);

        self::assertEquals($expectedCollection, $actualCollection);
    }

    public function test_it_returns_filtered_families_by_search(): void
    {
        $query = new FamilyQuery();
        $query->search = 'Bi';

        $expectedCollection = new FamilyWithLabelsCollection([
            new FamilyWithLabels('beers', ['fr_FR' => 'Bières']),
            new FamilyWithLabels('bikes', ['fr_FR' => 'Vélos', 'en_US' => 'Bikes']),
        ]);

        $actualCollection = $this->sqlFindFamiliesWithLabels->fromQuery($query);

        self::assertEquals($expectedCollection, $actualCollection);
    }

    public function test_it_returns_filtered_families_by_search_and_search_language(): void
    {
        $query = new FamilyQuery();
        $query->search = 'Bi';
        $query->searchLanguage = 'en_US';

        $expectedCollection = new FamilyWithLabelsCollection([
            new FamilyWithLabels('bikes', ['fr_FR' => 'Vélos', 'en_US' => 'Bikes']),
        ]);

        $actualCollection = $this->sqlFindFamiliesWithLabels->fromQuery($query);

        self::assertEquals($expectedCollection, $actualCollection);
    }

    public function test_it_returns_filtered_families_among_an_include_codes_list(): void
    {
        $query = new FamilyQuery();
        $query->search = 't';
        $query->includeCodes = ['bikes', 'tvs'];

        $expectedCollection = new FamilyWithLabelsCollection([
            new FamilyWithLabels('tvs', []),
        ]);

        $actualCollection = $this->sqlFindFamiliesWithLabels->fromQuery($query);

        self::assertEquals($expectedCollection, $actualCollection);
    }

    public function test_it_returns_filtered_families_among_an_empty_include_codes_list(): void
    {
        $query = new FamilyQuery();
        $query->search = 'Scr';
        $query->includeCodes = [];

        $expectedCollection = new FamilyWithLabelsCollection([]);

        $actualCollection = $this->sqlFindFamiliesWithLabels->fromQuery($query);

        self::assertEquals($expectedCollection, $actualCollection);
    }

    public function test_it_returns_filtered_families_with_an_empty_exclude_codes_list(): void
    {
        $query = new FamilyQuery();
        $query->search = 'Scr';
        $query->searchLanguage = 'en_US';

        $expectedCollection = new FamilyWithLabelsCollection([
            new FamilyWithLabels('screens', ['fr_FR' => 'Écrans', 'en_US' => 'Screens']),
        ]);

        $actualCollection = $this->sqlFindFamiliesWithLabels->fromQuery($query);

        self::assertEquals($expectedCollection, $actualCollection);
    }

    public function test_it_returns_filtered_families_with_exclude_codes(): void
    {
        $query = new FamilyQuery();
        $query->search = 'b';
        $query->excludeCodes = ['beers'];

        $expectedCollection = new FamilyWithLabelsCollection([
            new FamilyWithLabels('bikes', ['fr_FR' => 'Vélos', 'en_US' => 'Bikes']),
        ]);

        $actualCollection = $this->sqlFindFamiliesWithLabels->fromQuery($query);

        self::assertEquals($expectedCollection, $actualCollection);
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
