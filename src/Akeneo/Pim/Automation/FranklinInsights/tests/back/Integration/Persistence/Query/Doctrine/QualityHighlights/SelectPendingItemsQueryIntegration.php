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

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\Lock;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\QualityHighlights\SelectPendingItemsQuery;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine\QualityHighlights\PendingItemsRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class SelectPendingItemsQueryIntegration extends TestCase
{
    private const UNLOCKED = '';
    private const LOCKED = '42922021-cec9-4810-ac7a-ace3584f8671';

    public function test_it_returns_updated_attribute_ids()
    {
        $results = $this
            ->getQuery()
            ->getUpdatedAttributeCodes(new Lock(self::LOCKED), 100);
        $this->assertEmpty($results);

        $this->insertUpdatedAttribute('up_attr_1', self::LOCKED);
        $this->insertUpdatedAttribute('up_attr_2', self::UNLOCKED);
        $this->insertUpdatedAttribute('up_attr_3', self::LOCKED);
        $this->insertDeletedAttribute('del_attr_1', self::LOCKED);

        $results = $this
            ->getQuery()
            ->getUpdatedAttributeCodes(new Lock(self::LOCKED), 100);
        $this->assertSame(
            [
                'up_attr_1',
                'up_attr_3',
            ],
            $results
        );

        $results = $this
            ->getQuery()
            ->getUpdatedAttributeCodes(new Lock(self::LOCKED), 1);
        $this->assertSame(['up_attr_1'], $results);

    }

    public function test_it_returns_deleted_attribute_ids()
    {
        $results = $this
            ->getQuery()
            ->getDeletedAttributeCodes(new Lock(self::LOCKED), 100);
        $this->assertEmpty($results);

        $this->insertUpdatedAttribute('up_attr_3', self::UNLOCKED);
        $this->insertDeletedAttribute('del_attr_1', self::LOCKED);
        $this->insertDeletedAttribute('del_attr_2', self::UNLOCKED);
        $this->insertDeletedAttribute('del_attr_3', self::LOCKED);

        $results = $this
            ->getQuery()
            ->getDeletedAttributeCodes(new Lock(self::LOCKED), 100);
        $this->assertSame(
            [
                'del_attr_1',
                'del_attr_3'
            ],
            $results
        );

        $results = $this
            ->getQuery()
            ->getDeletedAttributeCodes(new Lock(self::LOCKED), 1);
        $this->assertSame(['del_attr_1'], $results);
    }

    private function insertUpdatedAttribute(string $attributeCode, string $lock)
    {
        $this->getFromTestContainer('database_connection')->executeQuery($this->getInsertSql(), [
            'entity_type' => PendingItemsRepository::ENTITY_TYPE_ATTRIBUTE,
            'entity_id' => $attributeCode,
            'action' => PendingItemsRepository::ACTION_ENTITY_UPDATED,
            'lock' => $lock,
        ]);
    }

    private function insertDeletedAttribute(string $attributeCode, string $lock)
    {
        $this->getFromTestContainer('database_connection')->executeQuery($this->getInsertSql(), [
            'entity_type' => PendingItemsRepository::ENTITY_TYPE_ATTRIBUTE,
            'entity_id' => $attributeCode,
            'action' => PendingItemsRepository::ACTION_ENTITY_DELETED,
            'lock' => $lock,
        ]);
    }

    private function getQuery(): SelectPendingItemsQuery
    {
        return $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_pending_items');

    }

    private function getInsertSql(): string
    {
        return <<<SQL
            INSERT INTO pimee_franklin_insights_quality_highlights_pending_items (entity_type, entity_id, `action`, lock_id)
            VALUES (:entity_type, :entity_id, :action, :lock);
SQL;
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
