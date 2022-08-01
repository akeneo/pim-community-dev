<?php

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Query\Family;

use Akeneo\Platform\TailoredImport\Domain\Query\Family\FindFamiliesResult;
use Akeneo\Platform\TailoredImport\Infrastructure\Query\Family\FindFamilies;
use Akeneo\Platform\TailoredImport\Test\Integration\IntegrationTestCase;
use Akeneo\Test\Integration\Configuration;

class FindFamiliesIntegration extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_creates_a_result_from_a_query_to_search_for_families()
    {
        $result = $this->getQuery()->execute('en_US', 10, search: 'a');

        $this->assertInstanceOf(FindFamiliesResult::class, $result);
        $this->assertEquals(3, $result->getMatchesCount());
        $this->assertEquals([
            'items' => [
                [
                    'code' => 'car',
                    'labels' => [
                        'en_US' => 'Car',
                        'de_DE' => 'Auto',
                    ],
                ],
                [
                    'code' => 'accessories',
                    'labels' => [
                        'en_US' => 'Accessories',
                        'fr_FR' => 'Accessoires',
                    ],
                ],
                [
                    'code' => 'magic_cards',
                    'labels' => [
                        'en_US' => 'Magic cards',
                        'fr_FR' => 'Cartes Magic',
                    ],
                ],
            ],
            'matches_count' => 3
        ], $result->normalize());
    }

    private function getQuery(): FindFamilies
    {
        return $this->get('akeneo.tailored_import.query.family.find_families');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
