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

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\QualityHighlights\SelectPendingAttributeIdsQuery;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine\QualityHighlights\PendingAttributesRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class SelectPendingAttributeIdsQueryIntegration extends TestCase
{
    public function test_it_returns_updated_attribute_ids()
    {
        $results = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_pending_attributes')
            ->getUpdatedAttributeIds(0, 100);
        $this->assertEmpty($results);

        $this->createDataSet();

        $results = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_pending_attributes')
            ->getUpdatedAttributeIds(0, 100);
        $this->assertSame([1, 3], $results);

        $results = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_pending_attributes')
            ->getUpdatedAttributeIds(0, 1);
        $this->assertSame([1], $results);

        $results = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_pending_attributes')
            ->getUpdatedAttributeIds(1, 100);
        $this->assertSame([3], $results);
    }

    public function test_it_returns_deleted_attribute_ids()
    {
        $results = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_pending_attributes')
            ->getDeletedAttributeIds(0, 100);
        $this->assertEmpty($results);

        $this->createDataSet();

        $results = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_pending_attributes')
            ->getDeletedAttributeIds(0, 100);
        $this->assertSame([4, 6], $results);

        $results = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_pending_attributes')
            ->getDeletedAttributeIds(0, 1);
        $this->assertSame([4], $results);

        $results = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_pending_attributes')
            ->getDeletedAttributeIds(1, 100);
        $this->assertSame([6], $results);
    }

    private function createDataSet(): void
    {
        $this->insertUpdatedAttribute(1, PendingAttributesRepository::STATUS_UNLOCKED);
        $this->insertUpdatedAttribute(2, PendingAttributesRepository::STATUS_LOCKED);
        $this->insertUpdatedAttribute(3, PendingAttributesRepository::STATUS_UNLOCKED);
        $this->insertDeletedAttribute(4, PendingAttributesRepository::STATUS_UNLOCKED);
        $this->insertDeletedAttribute(5, PendingAttributesRepository::STATUS_LOCKED);
        $this->insertDeletedAttribute(6, PendingAttributesRepository::STATUS_UNLOCKED);
    }

    private function insertUpdatedAttribute(int $attributeId, int $status)
    {
        $this->getFromTestContainer('database_connection')->executeQuery($this->getInsertSql(), [
            'entity_type' => PendingAttributesRepository::ENTITY_TYPE_ATTRIBUTE,
            'entity_id' => $attributeId,
            'action' => PendingAttributesRepository::ACTION_ATTRIBUTE_UPDATED,
            'status' => $status,
        ]);
    }

    private function insertDeletedAttribute(int $attributeId, int $status)
    {
        $this->getFromTestContainer('database_connection')->executeQuery($this->getInsertSql(), [
            'entity_type' => PendingAttributesRepository::ENTITY_TYPE_ATTRIBUTE,
            'entity_id' => $attributeId,
            'action' => PendingAttributesRepository::ACTION_ATTRIBUTE_DELETED,
            'status' => $status,
        ]);
    }

    private function getInsertSql(): string
    {
        return <<<SQL
            INSERT INTO pimee_franklin_insights_quality_highlights_pending_items (entity_type, entity_id, `action`, status)
            VALUES (:entity_type, :entity_id, :action, :status);
SQL;
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
