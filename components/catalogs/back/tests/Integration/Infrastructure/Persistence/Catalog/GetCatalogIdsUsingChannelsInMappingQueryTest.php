<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogIdsUsingChannelsInMappingQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogIdsUsingChannelsInMappingQuery
 */
final class GetCatalogIdsUsingChannelsInMappingQueryTest extends IntegrationTestCase
{
    private ?GetCatalogIdsUsingChannelsInMappingQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetCatalogIdsUsingChannelsInMappingQuery::class);
    }

    /**
     * @dataProvider catalogsByChannelsDataProvider
     */
    public function testItGetsCatalogsByChannel(
        array $firstMapping,
        array $secondMapping,
        array $channelsQueried,
        array $expectedCatalogs,
    ): void {
        $this->createUser('shopifi');

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
            catalogProductMapping: $firstMapping,
        );
        $this->createCatalog(
            id: 'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            name: 'Store FR',
            ownerUsername: 'shopifi',
            catalogProductMapping: $secondMapping,
        );
        $this->createCatalog(
            id: '27c53e59-ee6a-4215-a8f1-2fccbb67ba0d',
            name: 'Store UK',
            ownerUsername: 'shopifi',
        );

        $resultBothCatalogs = $this->query->execute($channelsQueried);
        $this->assertEquals($expectedCatalogs, $resultBothCatalogs);
    }

    public function catalogsByChannelsDataProvider(): array
    {
        return [
            'gets two catalogs with two channels' => [
                'first_mapping' => ['custom_field' => ['scope' => 'ecommerce', 'source' => 'meta_title']],
                'second_mapping' => ['custom_field' => ['scope' => 'print', 'source' => 'meta_title']],
                'channels_queried' => ['ecommerce', 'print'],
                'expected_catalog' => ['db1079b6-f397-4a6a-bae4-8658e64ad47c', 'ed30425c-d9cf-468b-8bc7-fa346f41dd07'],
            ],
            'gets two catalogs with one channel' => [
                'first_mapping' => [
                    'custom_field' => ['scope' => 'ecommerce', 'source' => 'meta_title'],
                    'custom_field2' => ['scope' => 'print', 'source' => 'meta_title'],
                ],
                'second_mapping' => ['custom_field' => ['scope' => 'print', 'source' => 'meta_title']],
                'channels_queried' => ['print'],
                'expected_catalog' => ['db1079b6-f397-4a6a-bae4-8658e64ad47c', 'ed30425c-d9cf-468b-8bc7-fa346f41dd07'],
            ],
            'gets only one catalog with one channel' => [
                'first_mapping' => [
                    'custom_field' => ['scope' => 'ecommerce', 'source' => 'meta_title'],
                    'custom_field2' => ['scope' => 'print', 'source' => 'meta_title'],
                ],
                'second_mapping' => ['custom_field' => ['scope' => 'print', 'source' => 'meta_title']],
                'channels_queried' => ['ecommerce'],
                'expected_catalog' => ['db1079b6-f397-4a6a-bae4-8658e64ad47c'],
            ],
            'gets no catalogs with one channel' => [
                'first_mapping' => [
                    'custom_field' => ['scope' => 'ecommerce', 'source' => 'meta_title'],
                    'custom_field2' => ['scope' => 'print', 'source' => 'meta_title'],
                ],
                'second_mapping' => ['custom_field' => ['scope' => 'print', 'source' => 'meta_title']],
                'channels_queried' => ['mobile'],
                'expected_catalog' => [],
            ],
        ];
    }
}
