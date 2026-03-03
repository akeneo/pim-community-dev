<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Symfony\Command\OneTimeTask;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\ProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command\OneTimeTask\OneTimeTaskCommandTrait;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use Ramsey\Uuid\UuidInterface;
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

        $productWithoutDeprecatedSCoresId = $this->createProduct('product_without_deprecated_scores')->getUuid();
        $productUuidA = $this->createProduct('product_A')->getUuid();
        $productUuidB = $this->createProduct('product_B')->getUuid();
        $productUuidC = $this->createProduct('product_C')->getUuid();

        $this->resetProductsScores();
        $this->allowMultipleScoresPerProduct();

        $this->insertProductScore($productWithoutDeprecatedSCoresId, $productWithoutDeprecatedSCoresDate);
        $this->insertProductScore($productUuidA, $productLastScoreDateA->modify('-1 DAY'));
        $this->insertProductScore($productUuidA, $productLastScoreDateA);
        $this->insertProductScore($productUuidB, $productLastScoreDateB->modify('-1 MONTH'));
        $this->insertProductScore($productUuidB, $productLastScoreDateB);
        $this->insertProductScore($productUuidC, $productLastScoreDateC->modify('-1 YEAR'));
        $this->insertProductScore($productUuidC, $productLastScoreDateC);

        $this->launchCleaning();

        $this->assertCountProductsScores(4);
        $this->assertProductScoreExists($productWithoutDeprecatedSCoresId, $productWithoutDeprecatedSCoresDate);
        $this->assertProductScoreExists($productUuidA, $productLastScoreDateA);
        $this->assertProductScoreExists($productUuidB, $productLastScoreDateB);
        $this->assertProductScoreExists($productUuidC, $productLastScoreDateC);

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

    private function assertProductScoreExists(UuidInterface $productUuid, \DateTimeImmutable $evaluatedAt): void
    {
        $productScoreExists = $this->dbConnection->executeQuery(<<<SQL
SELECT 1 FROM pim_data_quality_insights_product_score
WHERE product_uuid = :productUuid AND evaluated_at = :evaluatedAt;
SQL,
            [
                'productUuid' => $productUuid->getBytes(),
                'evaluatedAt' => $evaluatedAt->format('Y-m-d'),
            ]
        )->fetchOne();

        $this->assertSame('1', $productScoreExists, sprintf('Product %s should have a score evaluated at %s', $productUuid->toString(), $evaluatedAt->format('Y-m-d')));
    }

    private function insertProductScore(UuidInterface $productUuid, \DateTimeImmutable $evaluatedAt): void
    {
        $productScore = new ProductScores(
            ProductUuid::fromString($productUuid->toString()),
            $evaluatedAt,
            (new ChannelLocaleRateCollection())
                ->addRate(new ChannelCode('mobile'), new LocaleCode('en_US'), new Rate(42)),
            new ChannelLocaleRateCollection()
        );

        $insertQuery = <<<SQL
INSERT INTO pim_data_quality_insights_product_score (product_uuid, evaluated_at, scores)
VALUES (:productUuid, :evaluatedAt, :scores);
SQL;

        $this->dbConnection->executeQuery($insertQuery, [
            'productUuid' => $productScore->getEntityId()->toBytes(),
            'evaluatedAt' => $productScore->getEvaluatedAt()->format('Y-m-d'),
            'scores' => \json_encode($productScore->getScores()->toNormalizedRates()),
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
    ADD PRIMARY KEY (product_uuid, evaluated_at);
SQL
        );
    }

    private function allowOnlyOneScorePerProduct(): void
    {
        $this->dbConnection->executeQuery(<<<SQL
ALTER TABLE pim_data_quality_insights_product_score 
    DROP PRIMARY KEY, 
    ADD PRIMARY KEY (product_uuid);
SQL
        );
    }
}
