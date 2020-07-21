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

namespace Pimee\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_5_0_20200511122550_data_quality_insights_evaluations_on_structure_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_5_0_20200511122550_data_quality_insights_evaluations_on_structure';

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_migrates_schema_and_data_for_data_quality_insights_evaluations_on_structure()
    {
        $this->prepareMigration();
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertTableExists('pimee_dqi_attribute_spellcheck');
        $this->assertTableExists('pimee_dqi_attribute_option_spellcheck');
        $this->assertTableExists('pimee_dqi_attribute_quality');
        $this->assertAttributeOptionSpellingCriteriaHaveBeenCreated();
    }

    private function prepareMigration(): void
    {
        $this->get('database_connection')->executeQuery(<<<SQL
DROP TABLE IF EXISTS pimee_dqi_attribute_spellcheck;
DROP TABLE IF EXISTS pimee_dqi_attribute_option_spellcheck;
DROP TABLE IF EXISTS pimee_dqi_attribute_quality;
SQL
        );

        $this->createAFamilyWithTwoProducts();

        $this->get('database_connection')->executeQuery(<<<SQL
DELETE FROM pimee_data_quality_insights_product_criteria_evaluation
WHERE criterion_code = 'consistency_attribute_option_spelling';
SQL
        );
    }

    private function assertTableExists(string $tableName): void
    {
        $query = <<<SQL
SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = :tableName
SQL;

        $stmt = $this->get('database_connection')->executeQuery($query, [
           'tableName' => $tableName,
        ]);

        $this->assertTrue((bool) $stmt->fetchColumn(), sprintf('The table %s should exist', $tableName));
    }

    private function assertAttributeOptionSpellingCriteriaHaveBeenCreated(): void
    {
        $query = <<<SQL
SELECT COUNT(*) FROM pimee_data_quality_insights_product_criteria_evaluation
WHERE criterion_code = 'consistency_attribute_option_spelling';
SQL;
        $stmt = $this->get('database_connection')->executeQuery($query);

        $this->assertSame(2, intval($stmt->fetchColumn()), 'There should be two consistency_attribute_option_spelling criteria');
    }

    private function createAFamilyWithTwoProducts(): void
    {
        $family = $this->get('akeneo_ee_integration_tests.builder.family')->build([
            'code' => 'a_family',
        ]);

        $this->get('pim_catalog.saver.family')->save($family);

        $product = $this->get('pim_catalog.builder.product')->createProduct('a_product', 'a_family');
        $this->get('pim_catalog.saver.product')->save($product);

        $product = $this->get('pim_catalog.builder.product')->createProduct('another_product', 'a_family');
        $this->get('pim_catalog.saver.product')->save($product);
    }
}
