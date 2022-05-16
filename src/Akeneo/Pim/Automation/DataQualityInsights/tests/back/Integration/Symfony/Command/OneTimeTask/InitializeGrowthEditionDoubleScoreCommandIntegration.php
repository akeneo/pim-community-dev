<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Symfony\Command\OneTimeTask;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command\OneTimeTask\OneTimeTaskCommandTrait;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class InitializeGrowthEditionDoubleScoreCommandIntegration extends DataQualityInsightsTestCase
{
    use OneTimeTaskCommandTrait;

    private const COMMAND_NAME = 'pim:data-quality-insights:initialize-growth-edition-double-score';

    public function setUp(): void
    {
        parent::setUp();

        $this->dbConnection = $this->get('database_connection');
        $this->deleteTask(self::COMMAND_NAME);
    }

    public function test_it_initializes_double_score_criteria(): void
    {
        $this->givenProductsWithoutConsistencyCriteria(10);
        $this->givenProductModelsWithoutConsistencyCriteria(5);

        $this->assertCountProductsWithConsistencyCriteria(0);
        $this->assertCountProductModelsWithConsistencyCriteria(0);

        $statusCode = $this->launchCommand();
        $this->assertSame(0, $statusCode, 'The command should have been successful');

        $this->assertCountProductsWithConsistencyCriteria(10);
        $this->assertCountProductModelsWithConsistencyCriteria(5);
    }

    public function test_it_does_nothing_if_all_criteria_feature_is_enabled(): void
    {
        $this->get('feature_flags')->enable('data_quality_insights');
        $this->get('feature_flags')->enable('data_quality_insights_all_criteria');

        $statusCode = $this->launchCommand();

        $this->assertSame(1, $statusCode, 'The command should have failed');
    }

    private function launchCommand(): int
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find(self::COMMAND_NAME);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName(), '--bulk-size' => 2], ['capture_stderr_separately' => true]);

        return $commandTester->getStatusCode();
    }

    private function givenProductsWithoutConsistencyCriteria(int $nbProducts): void
    {
        for ($i = 1; $i <= $nbProducts; $i++) {
            $this->createProduct(sprintf('product_%d', $i));
        }

        $sql = <<<SQL
DELETE FROM pim_data_quality_insights_product_criteria_evaluation
WHERE criterion_code LIKE 'consistency_%';
SQL;

        $this->dbConnection->executeQuery($sql);
    }

    private function givenProductModelsWithoutConsistencyCriteria(int $nbProductModels): void
    {
        $this->createMinimalFamilyAndFamilyVariant('a_family', 'a_family_variant');

        for ($i = 1; $i <= $nbProductModels; $i++) {
            $this->createProductModel(sprintf('product_model_%d', $i), 'a_family_variant');
        }

        $sql = <<<SQL
DELETE FROM pim_data_quality_insights_product_model_criteria_evaluation
WHERE criterion_code LIKE 'consistency_%';
SQL;

        $this->dbConnection->executeQuery($sql);
    }

    private function assertCountProductsWithConsistencyCriteria($expectedCount): void
    {
        $sql = <<<SQL
SELECT COUNT(DISTINCT product_id)
FROM pim_data_quality_insights_product_criteria_evaluation
WHERE criterion_code LIKE 'consistency_%';
SQL;

        $count = $this->dbConnection->executeQuery($sql)->fetchOne();

        $this->assertSame($expectedCount, (int) $count, sprintf('There should be %d products with consistency criteria', $expectedCount));
    }

    private function assertCountProductModelsWithConsistencyCriteria($expectedCount): void
    {
        $sql = <<<SQL
SELECT COUNT(DISTINCT product_id)
FROM pim_data_quality_insights_product_model_criteria_evaluation
WHERE criterion_code LIKE 'consistency_%';
SQL;

        $count = $this->dbConnection->executeQuery($sql)->fetchOne();

        $this->assertSame($expectedCount, (int) $count, sprintf('There should be %d product models with consistency criteria', $expectedCount));
    }
}
