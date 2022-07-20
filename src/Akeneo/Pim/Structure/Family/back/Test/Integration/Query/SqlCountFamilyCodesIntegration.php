<?php

namespace Akeneo\Test\Pim\Structure\Family\Integration\Query;

use Akeneo\Pim\Structure\Family\ServiceAPI\Query\CountFamilyQuery;
use Akeneo\Pim\Structure\Family\ServiceAPI\Query\CountFamilyCodes;
use Akeneo\Pim\Structure\Family\Infrastructure\Query\SqlCountFamilyCodes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

class SqlCountFamilyCodesIntegration extends TestCase
{
    private SqlCountFamilyCodes $sqlCountFamilyCodes;

    public function setUp(): void
    {
        parent::setUp();
        $this->sqlCountFamilyCodes = $this->get(CountFamilyCodes::class);

        $this->createFamily('beers', ['fr_FR' => 'Bières']);
        $this->createFamily('bikes', ['fr_FR' => 'Vélos', 'en_US' => 'Bikes']);
        $this->createFamily('screens', ['fr_FR' => 'Écrans', 'en_US' => 'Screens']);
        $this->createFamily('tvs', []);
    }

    public function test_it_returns_count_of_all_families(): void
    {
        $query = new CountFamilyQuery();

        $expectedCount = 4;

        $actualCount = $this->sqlCountFamilyCodes->fromQuery($query);

        self::assertEqualsCanonicalizing($expectedCount, $actualCount);
    }

    public function test_it_returns_count_of_all_families_with_search_language_but_no_search(): void
    {
        $query = new CountFamilyQuery();
        $query->searchLanguage = 'fr_FR';

        $expectedCount = 4;

        $actualCount = $this->sqlCountFamilyCodes->fromQuery($query);

        self::assertEqualsCanonicalizing($expectedCount, $actualCount);
    }

    public function test_it_returns_count_of_filtered_families_by_search(): void
    {
        $query = new CountFamilyQuery();
        $query->search = 'Bi';

        $expectedCount = 2;

        $actualCount = $this->sqlCountFamilyCodes->fromQuery($query);

        self::assertEqualsCanonicalizing($expectedCount, $actualCount);
    }

    public function test_it_returns_count_of_filtered_families_by_search_and_search_language(): void
    {
        $query = new CountFamilyQuery();
        $query->search = 'Bi';
        $query->searchLanguage = 'en_US';

        $expectedCount = 1;

        $actualCount = $this->sqlCountFamilyCodes->fromQuery($query);

        self::assertEqualsCanonicalizing($expectedCount, $actualCount);
    }

    public function test_it_returns_count_of_filtered_families_among_an_include_codes_list(): void
    {
        $query = new CountFamilyQuery();
        $query->search = 't';
        $query->includeCodes = ['bikes', 'tvs'];

        $expectedCount = 1;

        $actualCount = $this->sqlCountFamilyCodes->fromQuery($query);

        self::assertEqualsCanonicalizing($expectedCount, $actualCount);
    }

    public function test_it_returns_count_of_filtered_families_among_an_empty_include_codes_list(): void
    {
        $query = new CountFamilyQuery();
        $query->search = 'Scr';
        $query->includeCodes = [];

        $expectedCount = 0;

        $actualCount = $this->sqlCountFamilyCodes->fromQuery($query);

        self::assertEqualsCanonicalizing($expectedCount, $actualCount);
    }

    public function test_it_returns_count_of_filtered_families_with_an_empty_exclude_codes_list(): void
    {
        $query = new CountFamilyQuery();
        $query->search = 'Scr';
        $query->searchLanguage = 'en_US';

        $expectedCount = 1;

        $actualCount = $this->sqlCountFamilyCodes->fromQuery($query);

        self::assertEqualsCanonicalizing($expectedCount, $actualCount);
    }

    public function test_it_returns_count_of_filtered_families_with_exclude_codes(): void
    {
        $query = new CountFamilyQuery();
        $query->search = 'b';
        $query->excludeCodes = ['beers'];

        $expectedCount = 1;

        $actualCount = $this->sqlCountFamilyCodes->fromQuery($query);

        self::assertEqualsCanonicalizing($expectedCount, $actualCount);
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
