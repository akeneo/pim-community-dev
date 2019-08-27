<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Repository\Doctrine\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Repository\PendingItemsRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine\QualityHighlights\PendingItemsRepository;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class PendingItemsRepositoryIntegration extends TestCase
{
    public function test_it_saves_an_updated_attribute_id(): void
    {
        $sqlQuery = 'SELECT * FROM pimee_franklin_insights_quality_highlights_pending_items';
        $updatedAttributes = $this->getDbConnection()->query($sqlQuery)->fetchAll();
        $this->assertCount(0, $updatedAttributes);

        $this->getRepository()->addUpdatedAttributeCode('weight');
        $sqlQuery = 'SELECT entity_type, entity_id, action, locked FROM pimee_franklin_insights_quality_highlights_pending_items';
        $updatedAttributes = $this->getDbConnection()->query($sqlQuery)->fetchAll();
        $this->assertCount(1, $updatedAttributes);
        $this->assertSame(
            [
                'entity_type' => PendingItemsRepository::ENTITY_TYPE_ATTRIBUTE,
                'entity_id' => 'weight',
                'action' => PendingItemsRepository::ACTION_ATTRIBUTE_UPDATED,
                'locked' => (string) PendingItemsRepository::STATUS_UNLOCKED,
            ],
            $updatedAttributes[0]
        );
    }

    public function test_it_updates_the_action_on_duplicated_entry()
    {
        $sqlQuery = <<<SQL
INSERT INTO pimee_franklin_insights_quality_highlights_pending_items(entity_type, entity_id, action)
VALUES (:entity_type, :entity_id, :action)
SQL;
        $bindParams = [
            'entity_type' => PendingItemsRepository::ENTITY_TYPE_ATTRIBUTE,
            'entity_id' => 'size',
            'action' => PendingItemsRepository::ACTION_ATTRIBUTE_DELETED,
            'locked' => PendingItemsRepository::STATUS_UNLOCKED,
        ];
        $this->getDbConnection()->executeQuery($sqlQuery, $bindParams);

        $this->getRepository()->addUpdatedAttributeCode('size');
        $sqlQuery = 'SELECT entity_type, entity_id, action, locked FROM pimee_franklin_insights_quality_highlights_pending_items';
        $updatedAttributes = $this->getDbConnection()->query($sqlQuery)->fetchAll();
        $this->assertCount(1, $updatedAttributes);
        $this->assertSame(
            [
                'entity_type' => PendingItemsRepository::ENTITY_TYPE_ATTRIBUTE,
                'entity_id' => 'size',
                'action' => PendingItemsRepository::ACTION_ATTRIBUTE_UPDATED,
                'locked' => (string) PendingItemsRepository::STATUS_UNLOCKED,
            ],
            $updatedAttributes[0]
        );
    }

    private function getRepository(): PendingItemsRepositoryInterface
    {
        return $this->get('akeneo.pim.automation.franklin_insights.repository.quality_highlights_pending_items');
    }

    private function getDbConnection(): Connection
    {
        return $this->get('database_connection');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
