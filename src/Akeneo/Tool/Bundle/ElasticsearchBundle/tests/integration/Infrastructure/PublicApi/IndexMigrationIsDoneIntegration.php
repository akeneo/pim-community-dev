<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\tests\integration\Infrastructure\PublicApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Domain\Model\IndexMigration;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Domain\Query\IndexMigrationRepositoryInterface;
use Akeneo\Tool\Component\Elasticsearch\PublicApi\Read\IndexMigrationIsDoneInterface;

class IndexMigrationIsDoneIntegration extends TestCase
{
    private const INDEX_ALIAS = 'index_alias';
    private const INDEX_HASH = '01dc9c40d93e300302c0bee80f7aaaa29f54d6e9';

    /**
     * @test
     */
    public function it_returns_false_if_index_migration_is_not_done()
    {
        $this->assertFalse(
            $this->getQuery()->byIndexAliasAndHash(self::INDEX_ALIAS, self::INDEX_HASH)
        );
    }

    /**
     * @test
     */
    public function it_returns_true_if_index_migration_is_not_done()
    {
        $this->markIndexMigrationAsDone();
        $this->assertTrue(
            $this->getQuery()->byIndexAliasAndHash(self::INDEX_ALIAS, self::INDEX_HASH)
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function markIndexMigrationAsDone()
    {
        $indexMigration = IndexMigration::create(
            self::INDEX_ALIAS,
            self::INDEX_HASH,
            \DateTimeImmutable::createFromFormat(\DateTimeInterface::ISO8601, '2021-08-04T01:53:22-0700'),
            'temporary_index_alias',
            'new_index_name',
        );

        $indexMigration->markAsDone();

        $this->getIndexMigrationRepository()->save($indexMigration);
    }

    private function getIndexMigrationRepository(): IndexMigrationRepositoryInterface
    {
        return $this->get('Akeneo\Tool\Bundle\ElasticsearchBundle\Infrastructure\Repository\IndexMigrationRepository');
    }

    private function getQuery(): IndexMigrationIsDoneInterface
    {
        return $this->get('Akeneo\Tool\Component\Elasticsearch\PublicApi\Read\IndexMigrationIsDoneInterface');
    }
}
