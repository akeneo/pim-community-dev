<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Integration\Infrastructure\Symfony\Command\OneTimeTask;

use Akeneo\Pim\Automation\DataQualityInsights\back\Infrastructure\Symfony\Command\OneTimeTask\OneTimeTaskCommandTrait;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\ProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
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
        $productIdA = $this->createProduct('product_A')->getId();
        $productIdB = $this->createProduct('product_B')->getId();

        $channelMobile = new ChannelCode('mobile');
        $localeEn = new LocaleCode('en_US');
        $localeFr = new LocaleCode('fr_FR');

        $productScoreA1 = new ProductScores(
            new ProductId($productIdA),
            new \DateTimeImmutable('2020-11-18'),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(96))
                ->addRate($channelMobile, $localeFr, new Rate(36)),
            new ChannelLocaleRateCollection()
        );
        $productScoreA2 = new ProductScores(
            new ProductId($productIdA),
            new \DateTimeImmutable('2020-11-17'),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(79))
                ->addRate($channelMobile, $localeFr, new Rate(12)),
            new ChannelLocaleRateCollection()
        );
        $productScoreA3 = new ProductScores(
            new ProductId($productIdA),
            new \DateTimeImmutable('2020-11-16'),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(89))
                ->addRate($channelMobile, $localeFr, new Rate(42)),
            new ChannelLocaleRateCollection()
        );
        $productScoreB = new ProductScores(
            new ProductId($productIdB),
            new \DateTimeImmutable('2020-11-16'),
            (new ChannelLocaleRateCollection())
                ->addRate($channelMobile, $localeEn, new Rate(71))
                ->addRate($channelMobile, $localeFr, new Rate(0)),
            new ChannelLocaleRateCollection()
        );

        $this->resetProductsScores();
        $this->insertProductScore($productScoreA1);
        $this->insertProductScore($productScoreA2);
        $this->insertProductScore($productScoreA3);
        $this->insertProductScore($productScoreB);

        $this->launchCleaning();

        $this->assertCountProductsScores(2);
        $this->assertProductScoreExists($productScoreA1);
        $this->assertProductScoreExists($productScoreB);
    }

    private function launchCleaning(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find(self::COMMAND_NAME);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()], ['capture_stderr_separately' => true]);

        self::assertEquals(0, $commandTester->getStatusCode(), $commandTester->getErrorOutput());
    }

    private function assertCountProductsScores(int $expectedCount): void
    {
        $countProductsScores = $this->get('database_connection')->executeQuery(<<<SQL
SELECT COUNT(*) FROM pim_data_quality_insights_product_score;
SQL
        )->fetchOne();

        $this->assertSame($expectedCount, intval($countProductsScores));
    }

    private function assertProductScoreExists(ProductScores $expectedProductScore): void
    {
        $productScore = $this->get('database_connection')->executeQuery(<<<SQL
SELECT * FROM pim_data_quality_insights_product_score
WHERE product_id = :productId AND evaluated_at = :evaluatedAt;
SQL,
            [
                'productId' => $expectedProductScore->getProductId()->toInt(),
                'evaluatedAt' => $expectedProductScore->getEvaluatedAt()->format('Y-m-d'),
            ]
        )->fetchAssociative();

        $this->assertNotEmpty($productScore);

        $expectedScore = $expectedProductScore->getScores()->mapWith(function (Rate $score) {
            return [
                'rank' => Rank::fromRate($score)->toInt(),
                'value' => $score->toInt(),
            ];
        });

        $this->assertEquals($expectedScore, json_decode($productScore['scores'], true));
    }

    private function insertProductScore(ProductScores $productScore): void
    {
        $insertQuery = <<<SQL
INSERT INTO pim_data_quality_insights_product_score (product_id, evaluated_at, scores)
VALUES (:productId, :evaluatedAt, :scores);
SQL;

        $this->get('database_connection')->executeQuery($insertQuery, [
            'productId' => $productScore->getProductId()->toInt(),
            'evaluatedAt' => $productScore->getEvaluatedAt()->format('Y-m-d'),
            'scores' => \json_encode($productScore->getScores()->toNormalizedRates()),
        ], [
            'productId' => \PDO::PARAM_INT,
        ]);
    }
}
