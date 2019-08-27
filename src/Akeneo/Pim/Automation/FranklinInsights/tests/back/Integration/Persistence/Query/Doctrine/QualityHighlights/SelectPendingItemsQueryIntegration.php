<?php

declare (strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Query\Doctrine\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\QualityHighlights\SelectPendingItemsQuery;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine\QualityHighlights\PendingItemsRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class SelectPendingItemsQueryIntegration extends TestCase
{
    public function test_it_returns_updated_attribute_ids()
    {
        $results = $this
            ->getQuery()
            ->getUpdatedAttributeCodes(0, 100);
        $this->assertEmpty($results);

        $updatedAttr1Id = $this->insertUpdatedAttribute('up_attr_1', PendingItemsRepository::STATUS_UNLOCKED);
        $updatedAttr2Id = $this->insertUpdatedAttribute('up_attr_2', PendingItemsRepository::STATUS_LOCKED);
        $updatedAttr3Id = $this->insertUpdatedAttribute('up_attr_3', PendingItemsRepository::STATUS_UNLOCKED);
        $this->insertDeletedAttribute('del_attr_1', PendingItemsRepository::STATUS_UNLOCKED);
        $this->insertDeletedAttribute('del_attr_2', PendingItemsRepository::STATUS_LOCKED);
        $this->insertDeletedAttribute('del_attr_3', PendingItemsRepository::STATUS_UNLOCKED);

        $results = $this
            ->getQuery()
            ->getUpdatedAttributeCodes(0, 100);
        $this->assertSame(
            [
                $updatedAttr1Id => 'up_attr_1',
                $updatedAttr3Id => 'up_attr_3',
            ],
            $results
        );

        $results = $this
            ->getQuery()
            ->getUpdatedAttributeCodes(0, 1);
        $this->assertSame([$updatedAttr1Id => 'up_attr_1'], $results);

        $results = $this
            ->getQuery()
            ->getUpdatedAttributeCodes($updatedAttr2Id, 100);
        $this->assertSame([$updatedAttr3Id => 'up_attr_3'], $results);
    }

    public function test_it_returns_deleted_attribute_ids()
    {
        $results = $this
            ->getQuery()
            ->getDeletedAttributeCodes(0, 100);
        $this->assertEmpty($results);

        $this->insertUpdatedAttribute('up_attr_1', PendingItemsRepository::STATUS_UNLOCKED);
        $this->insertUpdatedAttribute('up_attr_2', PendingItemsRepository::STATUS_LOCKED);
        $this->insertUpdatedAttribute('up_attr_3', PendingItemsRepository::STATUS_UNLOCKED);
        $deletedAttr1Id = $this->insertDeletedAttribute('del_attr_1', PendingItemsRepository::STATUS_UNLOCKED);
        $deletedAttr2Id = $this->insertDeletedAttribute('del_attr_2', PendingItemsRepository::STATUS_LOCKED);
        $deletedAttr3Id = $this->insertDeletedAttribute('del_attr_3', PendingItemsRepository::STATUS_UNLOCKED);

        $results = $this
            ->getQuery()
            ->getDeletedAttributeCodes(0, 100);
        $this->assertSame(
            [
                $deletedAttr1Id => 'del_attr_1',
                $deletedAttr3Id => 'del_attr_3'
            ],
            $results
        );

        $results = $this
            ->getQuery()
            ->getDeletedAttributeCodes(0, 1);
        $this->assertSame([$deletedAttr1Id => 'del_attr_1'], $results);

        $results = $this
            ->getQuery()
            ->getDeletedAttributeCodes($deletedAttr2Id, 100);
        $this->assertSame([$deletedAttr3Id => 'del_attr_3'], $results);
    }

    private function createDataSet(): void
    {
        $this->insertUpdatedAttribute('up_attr_1', PendingItemsRepository::STATUS_UNLOCKED);
        $this->updatedAttribute2Id = $this->insertUpdatedAttribute('up_attr_2', PendingItemsRepository::STATUS_LOCKED);
        $this->insertUpdatedAttribute('up_attr_3', PendingItemsRepository::STATUS_UNLOCKED);
        $this->insertDeletedAttribute('del_attr_1', PendingItemsRepository::STATUS_UNLOCKED);
        $this->deletedAttribute2Id = $this->insertDeletedAttribute('del_attr_2', PendingItemsRepository::STATUS_LOCKED);
        $this->insertDeletedAttribute('del_attr_3', PendingItemsRepository::STATUS_UNLOCKED);
    }

    private function insertUpdatedAttribute(string $attributeCode, int $locked)
    {
        $this->getFromTestContainer('database_connection')->executeQuery($this->getInsertSql(), [
            'entity_type' => PendingItemsRepository::ENTITY_TYPE_ATTRIBUTE,
            'entity_id' => $attributeCode,
            'action' => PendingItemsRepository::ACTION_ATTRIBUTE_UPDATED,
            'locked' => $locked,
        ]);

        return (int) $this->getFromTestContainer('database_connection')->lastInsertId();
    }

    private function insertDeletedAttribute(string $attributeCode, int $locked)
    {
        $this->getFromTestContainer('database_connection')->executeQuery($this->getInsertSql(), [
            'entity_type' => PendingItemsRepository::ENTITY_TYPE_ATTRIBUTE,
            'entity_id' => $attributeCode,
            'action' => PendingItemsRepository::ACTION_ATTRIBUTE_DELETED,
            'locked' => $locked,
        ]);

        return (int) $this->getFromTestContainer('database_connection')->lastInsertId();
    }

    private function getQuery(): SelectPendingItemsQuery
    {
        return $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_pending_items');

    }

    private function getInsertSql(): string
    {
        return <<<SQL
            INSERT INTO pimee_franklin_insights_quality_highlights_pending_items (entity_type, entity_id, `action`, locked)
            VALUES (:entity_type, :entity_id, :action, :locked);
SQL;
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
