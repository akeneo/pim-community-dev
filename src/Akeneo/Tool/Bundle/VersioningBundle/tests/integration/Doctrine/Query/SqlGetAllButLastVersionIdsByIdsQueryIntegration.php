<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\VersioningBundle\tests\integration\Doctrine\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\VersioningBundle\Doctrine\Query\SqlGetAllButLastVersionIdsByIdsQuery;
use Akeneo\Tool\Component\Versioning\Model\Version;
use Ramsey\Uuid\Uuid;

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
                'resource_uuid' => Uuid::fromString('dc9ac794-fdfb-49e6-8a24-f01e0f68907d'),
                'version' => 1
            ],
            'product_42_second_version' => [
                'resource_name' => Product::class,
                'resource_id' => 42,
                'resource_uuid' => Uuid::fromString('dc9ac794-fdfb-49e6-8a24-f01e0f68907d'),
                'version' => 2
            ],
            'product_42_last_version' => [
                'resource_name' => Product::class,
                'resource_id' => 42,
                'resource_uuid' => Uuid::fromString('dc9ac794-fdfb-49e6-8a24-f01e0f68907d'),
                'version' => 3
            ],
            'product_123_unique_version' => [
                'resource_name' => Product::class,
                'resource_id' => 123,
                'resource_uuid' => Uuid::fromString('b63ca147-c9ff-49e9-9241-ad7ac84ef5b2'),
                'version' => 1
            ],
            'product_456_not_requested_last_version' => [
                'resource_name' => Attribute::class,
                'resource_id' => 456,
                'resource_uuid' => Uuid::fromString('d0982073-a5aa-4ace-935b-52e4af7d6ebd'),
                'version' => 2
            ],
            'attribute_25_first_version' => [
                'resource_name' => Attribute::class,
                'resource_id' => 25,
                'resource_uuid' => Uuid::fromString('223f8f74-6e2b-4489-8d28-3a82fd235285'),
                'version' => 1
            ],
            'attribute_25_last_version' => [
                'resource_name' => Attribute::class,
                'resource_id' => 25,
                'resource_uuid' => Uuid::fromString('223f8f74-6e2b-4489-8d28-3a82fd235285'),
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
            $version = new Version($versionData['resource_name'], $versionData['resource_id'], $versionData['resource_uuid'], 'system');
            $version->setVersion($versionData['version']);
            $entityManager->persist($version);
            $entityManager->flush();

            return $version->getId();
        }, $versions);
    }
}
