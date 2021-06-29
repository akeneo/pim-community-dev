<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\VersioningBundle\tests\integration\Doctrine\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\VersioningBundle\Doctrine\Query\SqlGetPurgeableVersionListQuery;
use Akeneo\Tool\Component\Versioning\Model\Version;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetPurgeableVersionListQueryIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_returns_the_ids_of_the_purgeable_versions_younger_than_a_given_date(): void
    {
        $limitDate = new \DateTime('now');

        $youngProductVersionIds = $this->givenProductVersionsAtLeastAsYoungAs($limitDate, 12);
        $this->givenProductVersionsOlderThan($limitDate, 2);
        $this->givenAttributeVersionYoungerThan($limitDate);

        $purgeableVersionsIdsCount = $this->getQuery()->countByResource(Product::class);
        $this->assertSame(14, $purgeableVersionsIdsCount, 'There should be 14 purgeable resources');

        $purgeableVersionsIds = [];
        $purgeableVersionsLists = $this->getQuery()->youngerThan(Product::class, $limitDate, 10);
        foreach ($purgeableVersionsLists as $purgeableVersionList) {
            $purgeableVersionsIds = array_merge($purgeableVersionsIds, $purgeableVersionList->getVersionIds());
        }

        $this->assertEquals($youngProductVersionIds, $purgeableVersionsIds, 'All youngest versions, and only they, should be returned');
    }

    /**
     * @test
     */
    public function it_returns_the_ids_of_the_purgeable_versions_older_than_a_given_date(): void
    {
        $limitDate = new \DateTime('now');

        $oldProductVersionIds = $this->givenProductVersionsOlderThan($limitDate, 12);
        $this->givenProductVersionsAtLeastAsYoungAs($limitDate, 2);
        $this->givenAttributeVersionOlderThan($limitDate);

        $purgeableVersionsIdsCount = $this->getQuery()->countByResource(Product::class);
        $this->assertSame(14, $purgeableVersionsIdsCount, 'There should be 14 purgeable versions');

        $purgeableVersionsIds = [];
        $purgeableVersionsLists = $this->getQuery()->olderThan(Product::class, $limitDate, 10);
        foreach ($purgeableVersionsLists as $purgeableVersionList) {
            $purgeableVersionsIds = array_merge($purgeableVersionsIds, $purgeableVersionList->getVersionIds());
        }

        $this->assertEqualsCanonicalizing(
            $oldProductVersionIds,
            $purgeableVersionsIds,
            'All oldest versions, and only they, should be returned',
        );
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

    private function getQuery(): SqlGetPurgeableVersionListQuery
    {
        return $this->get('pim_versioning.query.get_purgeable_version_list');
    }

    private function givenProductVersionsAtLeastAsYoungAs(\DateTime $limitDate, int $count): array
    {
        $loggedAt = clone $limitDate;
        $versionIds = [];
        for ($i = 0; $i < $count; $i++) {
            $versionIds[] = $this->createVersion(Product::class, 42, $loggedAt);
            $loggedAt->modify('+1 DAY');
        }

        return $versionIds;
    }

    private function givenProductVersionsOlderThan(\DateTime $limitDate, int $count): array
    {
        $versionIds = [];
        for ($i = $count; $i > 0; $i--) {
            $loggedAt = clone $limitDate;
            $loggedAt->modify(sprintf('-%d DAY', $i));
            $versionIds[] = $this->createVersion(Product::class, 42, $loggedAt);
        }

        return $versionIds;
    }

    private function givenAttributeVersionYoungerThan(\DateTime $limitDate): void
    {
        $loggedAt = clone $limitDate;
        $loggedAt->modify('+1 DAY');

        $this->createVersion(Attribute::class, 123, $loggedAt);
    }

    private function givenAttributeVersionOlderThan(\DateTime $limitDate): void
    {
        $loggedAt = clone $limitDate;
        $loggedAt->modify('-1 DAY');

        $this->createVersion(Attribute::class, 123, $loggedAt);
    }

    private function createVersion(string $resourceName, int $resourceId, \DateTime $loggedAt): int
    {
        $entityManager = $this->get('doctrine.orm.default_entity_manager');

        $version = new Version($resourceName, $resourceId, 'system');
        $entityManager->persist($version);
        $entityManager->flush();

        $this->get('database_connection')->executeQuery(
            'UPDATE pim_versioning_version SET logged_at = :logged_at WHERE id = :version_id',
            [
                'logged_at' => $loggedAt->format('Y-m-d H:i:s'),
                'version_id' => $version->getId(),
            ],
            [
                'logged_at' => \PDO::PARAM_STR,
                'version_id' => \PDO::PARAM_INT,
            ]
        );

        return $version->getId();
    }
}
