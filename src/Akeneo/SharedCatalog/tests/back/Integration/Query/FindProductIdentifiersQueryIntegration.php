<?php

namespace Akeneo\SharedCatalog\tests\back\Integration\Query;

use Akeneo\SharedCatalog\Model\SharedCatalog;
use Akeneo\SharedCatalog\Query\FindProductIdentifiersQueryInterface;
use Akeneo\Test\Integration\TestCase;

class FindProductIdentifiersQueryIntegration extends TestCase
{
    /** @var FindProductIdentifiersQueryInterface */
    private $findProductIdentifiersQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->findProductIdentifiersQuery = $this->get(FindProductIdentifiersQueryInterface::class);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    private function getSharedCatalogForWomenShirts(): SharedCatalog
    {
        return new SharedCatalog(
            'shared_catalog',
            'julia',
            [],
            [
                'data' => [
                    [
                        'field' => 'enabled',
                        'operator' => '=',
                        'value' => true,
                    ],
                    [
                        'field' => 'completeness',
                        'operator' => 'ALL',
                        'value' => 100,
                        'context' => [
                            'locales' => [
                                'de_DE',
                            ],
                        ],
                    ],
                    [
                        'field' => 'categories',
                        'operator' => 'IN',
                        'value' => [
                            'master_women_shirts',
                        ],
                    ],
                ],
            ],
            []
        );
    }

    /**
     * @test
     */
    public function it_find_all_product_identifiers_matching_a_shared_catalog()
    {
        $sharedCatalog = $this->getSharedCatalogForWomenShirts();

        $results = $this->findProductIdentifiersQuery->find($sharedCatalog, [
            'limit' => 100,
        ]);

        self::assertEquals([
            '1111111225',
            '1111111226',
            '1111111227',
            '1111111228',
            '1111111244',
            '1111111245',
            '1111111246',
            '1111111293',
            '1111111294',
            '1111111295',
            '1111111296',
            '1111111297',
            '1111111298',
            '1111111299',
            '1111111300',
            '1111111301',
        ], $results);
    }

    /**
     * @test
     */
    public function it_can_paginate_product_identifiers_matching_a_shared_catalog()
    {
        $sharedCatalog = $this->getSharedCatalogForWomenShirts();

        $results = $this->findProductIdentifiersQuery->find($sharedCatalog, [
            'limit' => 2,
        ]);

        self::assertEquals([
            '1111111225',
            '1111111226',
        ], $results);

        $results = $this->findProductIdentifiersQuery->find($sharedCatalog, [
            'limit' => 2,
            'search_after' => '1111111226',
        ]);

        self::assertEquals([
            '1111111227',
            '1111111228',
        ], $results);
    }
}
