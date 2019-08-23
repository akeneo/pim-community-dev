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

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\QualityHighlights\SelectPendingAttributesQuery;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class SelectPendingAttributesQueryIntegration extends TestCase
{
    public function test_it_returns_updated_attribute_ids()
    {
        $results = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_pending_attributes')
            ->getUpdatedAttributeIds();
        $this->assertEmpty($results);

        $this->insertUpdatedAttribute(1, SelectPendingAttributesQuery::STATUS_UNLOCKED);
        $this->insertUpdatedAttribute(2, SelectPendingAttributesQuery::STATUS_LOCKED);
        $this->insertUpdatedAttribute(3, SelectPendingAttributesQuery::STATUS_UNLOCKED);
        $this->insertDeletedAttribute(4, SelectPendingAttributesQuery::STATUS_UNLOCKED);
        $this->insertDeletedAttribute(5, SelectPendingAttributesQuery::STATUS_LOCKED);

        $results = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_pending_attributes')
            ->getUpdatedAttributeIds();

        $this->assertSame([1, 3], $results);
    }

    private function insertUpdatedAttribute(int $attributeId, int $status)
    {
        $this->getFromTestContainer('database_connection')->executeQuery($this->getInsertSql(), [
            'entity_type' => SelectPendingAttributesQuery::ENTITY_TYPE_ATTRIBUTE,
            'entity_id' => $attributeId,
            'action' => SelectPendingAttributesQuery::ACTION_ATTRIBUTE_UPDATED,
            'status' => $status,
        ]);
    }

    private function insertDeletedAttribute(int $attributeId, int $status)
    {
        $this->getFromTestContainer('database_connection')->executeQuery($this->getInsertSql(), [
            'entity_type' => SelectPendingAttributesQuery::ENTITY_TYPE_ATTRIBUTE,
            'entity_id' => $attributeId,
            'action' => SelectPendingAttributesQuery::ACTION_ATTRIBUTE_DELETED,
            'status' => $status,
        ]);
    }

    private function getInsertSql(): string
    {
        return <<<SQL
            INSERT INTO pimee_franklin_insights_pending_items (entity_type, entity_id, `action`, status)
            VALUES (:entity_type, :entity_id, :action, :status);
SQL;
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
