<?php

namespace Akeneo\Test\Pim\Structure\Family\Integration\Query;

use Akeneo\Pim\Structure\Family\API\Query\FamilyQuery;
use Akeneo\Pim\Structure\Family\API\Query\FindFamilyCodes;
use Akeneo\Pim\Structure\Family\Infrastructure\Query\SqlFindFamilyCodes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

/**
 * @group ce
 */
class SqlFindFamilyCodesIntegration extends TestCase
{
    private SqlFindFamilyCodes $sqlFindFamilyCodes;

    public function setUp(): void
    {
        parent::setUp();
        $this->sqlFindFamilyCodes = $this->get(FindFamilyCodes::class);

        $this->createFamily('beers', ['fr_FR' => 'Bières']);
        $this->createFamily('bikes', ['fr_FR' => 'Vélos', 'en_US' => 'Bikes']);
        $this->createFamily('screens', ['fr_FR' => 'Écrans', 'en_US' => 'Screens']);
        $this->createFamily('tvs', []);
    }

    public function test_it_returns_code_of_all_families(): void
    {
        $query = new FamilyQuery();

        $expectedCodes = ['beers', 'bikes', 'screens', 'tvs'];

        $actualCodes = $this->sqlFindFamilyCodes->fromQuery($query);

        self::assertEquals($expectedCodes, $actualCodes);
    }

    public function test_it_returns_code_of_all_families_with_search_language_but_no_search(): void
    {
        $query = new FamilyQuery();
        $query->searchLanguage = 'fr_FR';

        $expectedCodes = ['beers', 'bikes', 'screens', 'tvs'];

        $actualCodes = $this->sqlFindFamilyCodes->fromQuery($query);

        self::assertEquals($expectedCodes, $actualCodes);
    }

    public function test_it_returns_code_of_filtered_families_by_search(): void
    {
        $query = new FamilyQuery();
        $query->search = 'Bi';

        $expectedCodes = ['beers', 'bikes'];

        $actualCodes = $this->sqlFindFamilyCodes->fromQuery($query);

        self::assertEquals($expectedCodes, $actualCodes);
    }

    public function test_it_returns_code_of_filtered_families_by_search_and_search_language(): void
    {
        $query = new FamilyQuery();
        $query->search = 'Bi';
        $query->searchLanguage = 'en_US';

        $expectedCodes = ['bikes'];

        $actualCodes = $this->sqlFindFamilyCodes->fromQuery($query);

        self::assertEquals($expectedCodes, $actualCodes);
    }

    public function test_it_returns_code_of_filtered_families_among_an_include_codes_list(): void
    {
        $query = new FamilyQuery();
        $query->search = 't';
        $query->includeCodes = ['bikes', 'tvs'];

        $expectedCodes = ['tvs'];

        $actualCodes = $this->sqlFindFamilyCodes->fromQuery($query);

        self::assertEquals($expectedCodes, $actualCodes);
    }

    public function test_it_returns_code_of_filtered_families_among_an_empty_include_codes_list(): void
    {
        $query = new FamilyQuery();
        $query->search = 'Scr';
        $query->includeCodes = [];

        $expectedCodes = [];

        $actualCodes = $this->sqlFindFamilyCodes->fromQuery($query);

        self::assertEquals($expectedCodes, $actualCodes);
    }

    public function test_it_returns_code_of_filtered_families_with_an_empty_exclude_codes_list(): void
    {
        $query = new FamilyQuery();
        $query->search = 'Scr';
        $query->searchLanguage = 'en_US';

        $expectedCodes = ['screens'];

        $actualCodes = $this->sqlFindFamilyCodes->fromQuery($query);

        self::assertEquals($expectedCodes, $actualCodes);
    }

    public function test_it_returns_code_of_filtered_families_with_exclude_codes(): void
    {
        $query = new FamilyQuery();
        $query->search = 'b';
        $query->excludeCodes = ['beers'];

        $expectedCodes = ['bikes'];

        $actualCodes = $this->sqlFindFamilyCodes->fromQuery($query);

        self::assertEquals($expectedCodes, $actualCodes);
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
