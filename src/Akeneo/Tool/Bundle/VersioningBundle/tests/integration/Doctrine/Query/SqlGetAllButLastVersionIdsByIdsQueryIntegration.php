<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\VersioningBundle\tests\integration\Doctrine\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\VersioningBundle\Doctrine\Query\SqlGetAllButLastVersionIdsByIdsQuery;
use Akeneo\Tool\Component\Versioning\Model\Version;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetAllButLastVersionIdsByIdsQueryIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_returns_all_ids_but_the_latest_versions(): void
    {
        $versionIds = $this->createVersions([
            'product_42_first_version' => [
                'resource_name' => Product::class,
                'resource_id' => 42,
                'version' => 1
            ],
            'product_42_second_version' => [
                'resource_name' => Product::class,
                'resource_id' => 42,
                'version' => 2
            ],
            'product_42_last_version' => [
                'resource_name' => Product::class,
                'resource_id' => 42,
                'version' => 3
            ],
            'product_123_unique_version' => [
                'resource_name' => Product::class,
                'resource_id' => 123,
                'version' => 1
            ],
            'product_456_not_requested_last_version' => [
                'resource_name' => Attribute::class,
                'resource_id' => 456,
                'version' => 2
            ],
            'attribute_25_first_version' => [
                'resource_name' => Attribute::class,
                'resource_id' => 25,
                'version' => 1
            ],
            'attribute_25_last_version' => [
                'resource_name' => Attribute::class,
                'resource_id' => 25,
                'version' => 2
            ],
        ]);

        unset($versionIds['product_456_not_requested_last_version']);

        $expectedIds = [
            $versionIds['product_42_first_version'],
            $versionIds['product_42_second_version'],
            $versionIds['attribute_25_first_version'],
        ];

        $latestVersionIds = $this->getQuery()->execute($versionIds);

        $this->assertSame($expectedIds, $latestVersionIds);
    }

    /**
     * @inheritDoc
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->get('database_connection')->executeQuery('DELETE FROM pim_versioning_version');
    }

    private function getQuery(): SqlGetAllButLastVersionIdsByIdsQuery
    {
        return $this->get('pim_versioning.query.get_latest_version_ids_by_ids');
    }

    private function createVersions(array $versions): array
    {
        $entityManager = $this->get('doctrine.orm.default_entity_manager');

        return array_map(function ($versionData) use ($entityManager) {
            $version = new Version($versionData['resource_name'], $versionData['resource_id'], 'system');
            $version->setVersion($versionData['version']);
            $entityManager->persist($version);
            $entityManager->flush();

            return $version->getId();
        }, $versions);
    }
}
