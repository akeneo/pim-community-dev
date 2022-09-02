<?php

namespace Akeneo\SharedCatalog\tests\back\Integration\Query;

use Akeneo\SharedCatalog\Model\SharedCatalog;
use Akeneo\SharedCatalog\Query\FindProductUuidsQueryInterface;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class FindProductUuidsQueryIntegration extends TestCase
{
    private FindProductUuidsQueryInterface $findProductUuidsQuery;
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->findProductUuidsQuery = $this->get(FindProductUuidsQueryInterface::class);
        $this->connection = $this->get('database_connection');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    private function getSharedCatalogForWomenShirts(): SharedCatalog
    {
        return new SharedCatalog(
            'shared_catalog',
            'Shared catalog',
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
    public function it_requires_the_limit_option()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->findProductUuidsQuery->find(
            $this->getSharedCatalogForWomenShirts(),
            []
        );
    }

    /** @test */
    public function it_requires_a_valid_limit_option()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->findProductUuidsQuery->find(
            $this->getSharedCatalogForWomenShirts(),
            ['limit' => 'foo']
        );
    }

    /** @test */
    public function it_find_all_product_uuids_matching_a_shared_catalog()
    {
        $sharedCatalog = $this->getSharedCatalogForWomenShirts();

        $results = $this->findProductUuidsQuery->find($sharedCatalog, [
            'limit' => 100,
        ]);

        $uuids = $this->connection->executeQuery('SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product')
            ->fetchFirstColumn();
        sort($uuids);
        self::assertEquals(\array_slice($uuids, 0, 100), $results);
    }

    /** @test */
    public function it_can_paginate_product_uuids_matching_a_shared_catalog()
    {
        $sharedCatalog = $this->getSharedCatalogForWomenShirts();

        $uuids = $this->connection->executeQuery('SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product')
            ->fetchFirstColumn();
        sort($uuids);

        $results = $this->findProductUuidsQuery->find($sharedCatalog, [
            'limit' => 2,
        ]);

        self::assertEquals(\array_slice($uuids, 0, 2), $results);

        $results = $this->findProductUuidsQuery->find($sharedCatalog, [
            'limit' => 2,
            'search_after' => $uuids[10],
        ]);

        self::assertEquals(\array_slice($uuids, 11, 2), $results);
    }
}
