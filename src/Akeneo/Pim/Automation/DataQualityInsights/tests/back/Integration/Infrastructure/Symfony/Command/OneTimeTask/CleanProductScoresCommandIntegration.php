<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Integration\Infrastructure\Symfony\Command\OneTimeTask;

use Akeneo\Pim\Automation\DataQualityInsights\back\Infrastructure\Symfony\Command\OneTimeTask\OneTimeTaskCommandTrait;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\ProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CleanProductScoresCommandIntegration extends DataQualityInsightsTestCase
{
    use OneTimeTaskCommandTrait;

    private const COMMAND_NAME = 'pim:data-quality-insights:clean-product-scores';

    public function setUp(): void
    {
        parent::setUp();

        $this->dbConnection = $this->get('database_connection');
        $this->deleteTask(self::COMMAND_NAME);
    }

    public function test_it_cleans_product_scores(): void
    {
        $productWithoutDeprecatedSCoresDate = new \DateTimeImmutable('2020-10-19');
        $productLastScoreDateA = new \DateTimeImmutable('2020-11-18');
        $productLastScoreDateB = new \DateTimeImmutable('2020-11-17');
        $productLastScoreDateC = new \DateTimeImmutable('2020-10-11');

        $productWithoutDeprecatedSCoresId = $this->createProduct('product_without_deprecated_scores')->getId();
        $productIdA = $this->createProduct('product_A')->getId();
        $productIdB = $this->createProduct('product_B')->getId();
        $productIdC = $this->createProduct('product_C')->getId();

        $this->resetProductsScores();
        $this->allowMultipleScoresPerProduct();

        $this->insertProductScore($productWithoutDeprecatedSCoresId, $productWithoutDeprecatedSCoresDate);
        $this->insertProductScore($productIdA, $productLastScoreDateA->modify('-1 DAY'));
        $this->insertProductScore($productIdA, $productLastScoreDateA);
        $this->insertProductScore($productIdB, $productLastScoreDateB->modify('-1 MONTH'));
        $this->insertProductScore($productIdB, $productLastScoreDateB);
        $this->insertProductScore($productIdC, $productLastScoreDateC->modify('-1 YEAR'));
        $this->insertProductScore($productIdC, $productLastScoreDateC);

        $this->launchCleaning();

        $this->assertCountProductsScores(4);
        $this->assertProductScoreExists($productWithoutDeprecatedSCoresId, $productWithoutDeprecatedSCoresDate);
        $this->assertProductScoreExists($productIdA, $productLastScoreDateA);
        $this->assertProductScoreExists($productIdB, $productLastScoreDateB);
        $this->assertProductScoreExists($productIdC, $productLastScoreDateC);

        $this->allowOnlyOneScorePerProduct();
    }

    private function launchCleaning(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find(self::COMMAND_NAME);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName(), '--bulk-size' => 2], ['capture_stderr_separately' => true]);

        self::assertEquals(0, $commandTester->getStatusCode(), $commandTester->getErrorOutput());
    }

    private function assertCountProductsScores(int $expectedCount): void
    {
        $countProductsScores = $this->dbConnection->executeQuery(<<<SQL
SELECT COUNT(*) FROM pim_data_quality_insights_product_score;
SQL
        )->fetchOne();

        $this->assertSame($expectedCount, intval($countProductsScores), sprintf('There should be %d product scores', $expectedCount));
    }

    private function assertProductScoreExists(int $productId, \DateTimeImmutable $evaluatedAt): void
    {
        $productScoreExists = $this->dbConnection->executeQuery(<<<SQL
SELECT 1 FROM pim_data_quality_insights_product_score
WHERE product_id = :productId AND evaluated_at = :evaluatedAt;
SQL,
            [
                'productId' => $productId,
                'evaluatedAt' => $evaluatedAt->format('Y-m-d'),
            ]
        )->fetchOne();

        $this->assertSame('1', $productScoreExists, sprintf('Product %d should have a score evaluated at %s', $productId, $evaluatedAt->format('Y-m-d')));
    }

    private function insertProductScore(int $productId, \DateTimeImmutable $evaluatedAt): void
    {
        $productScore = new ProductScores(
            new ProductId($productId),
            $evaluatedAt,
            (new ChannelLocaleRateCollection())
                ->addRate(new ChannelCode('mobile'), new LocaleCode('en_US'), new Rate(42)),
            new ChannelLocaleRateCollection()
        );

        $insertQuery = <<<SQL
INSERT INTO pim_data_quality_insights_product_score (product_id, evaluated_at, scores)
VALUES (:productId, :evaluatedAt, :scores);
SQL;

        $this->dbConnection->executeQuery($insertQuery, [
            'productId' => $productScore->getProductId()->toInt(),
            'evaluatedAt' => $productScore->getEvaluatedAt()->format('Y-m-d'),
            'scores' => \json_encode($productScore->getScores()->toNormalizedRates()),
        ], [
            'productId' => \PDO::PARAM_INT,
        ]);
    }

    private function allowMultipleScoresPerProduct(): void
    {
        $indexes = $this->dbConnection->getSchemaManager()->listTableIndexes('pim_data_quality_insights_product_score');

        if (count($indexes['primary']?->getColumns()) > 1) {
            return;
        }

        $this->dbConnection->executeQuery(<<<SQL
ALTER TABLE pim_data_quality_insights_product_score 
    DROP PRIMARY KEY, 
    ADD PRIMARY KEY (product_id, evaluated_at);
SQL
        );
    }

    private function allowOnlyOneScorePerProduct(): void
    {
        $this->dbConnection->executeQuery(<<<SQL
ALTER TABLE pim_data_quality_insights_product_score 
    DROP PRIMARY KEY, 
    ADD PRIMARY KEY (product_id);
SQL
        );
    }
}
