<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Upgrade\test_schema;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Test\Integration\TestCase;

final class Version_4_0_20200129102033_franklin_insights_fill_pending_items_Integration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->createOneAttribute();
        $this->createOneFamily();
        $this->createOneProduct();
    }

    public function test_it_dose_not_fill_the_pending_items_if_Franklin_is_not_activated()
    {
        $this->runMigration();
        $this->assertPendingItemsAreEmpty();
    }

    public function test_it_fills_the_pending_items_if_Franklin_is_activated()
    {
        $this->activateFranklin();
        $this->assertPendingItemsAreEmpty();
        $this->runMigration();
        $this->assertPendingItemsAreFilled();
    }

    private function activateFranklin(): void
    {
        $this->get('pim_catalog.command_launcher')->executeForeground('pimee:franklin-insights:init-franklin-user');

        $configuration = new Configuration();
        $configuration->setToken(new Token('a_token'));

        $this->get('akeneo.pim.automation.franklin_insights.repository.configuration')->save($configuration);
    }

    private function countPendingItems(): int
    {
        $query = <<<SQL
SELECT COUNT(*) as nb_items
FROM pimee_franklin_insights_quality_highlights_pending_items
SQL;

        $stmt = $this->get('database_connection')->executeQuery($query);

        return intval($stmt->fetchColumn());
    }

    private function assertPendingItemsAreEmpty(): void
    {
        $this->assertSame(0, $this->countPendingItems());
    }

    private function assertPendingItemsAreFilled(): void
    {
        $this->assertSame(3, $this->countPendingItems());
    }

    private function createOneAttribute(): void
    {
        $attribute = $this->get('akeneo_ee_integration_tests.builder.attribute')->build([
            'code' => 'an_attribute',
            'type' => AttributeTypes::TEXT,
            'group' => AttributeGroup::DEFAULT_GROUP_CODE,
        ]);

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createOneFamily(): void
    {
        $family = $this->get('akeneo_ee_integration_tests.builder.family')->build([
            'code' => 'a_family',
            'attributes' => ['sku'],
        ]);

        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function createOneProduct(): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct('a_product', 'a_family');
        $this->get('validator')->validate($product);
        $this->get('pim_catalog.saver.product')->save($product);
    }

    private function runMigration(): void
    {
        $migrationCommand = sprintf('doctrine:migrations:execute %s --up -n', $this->getMigrationLabel());
        $this->get('pim_catalog.command_launcher')->executeForeground($migrationCommand);
    }

    private function getMigrationLabel(): string
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }
}
