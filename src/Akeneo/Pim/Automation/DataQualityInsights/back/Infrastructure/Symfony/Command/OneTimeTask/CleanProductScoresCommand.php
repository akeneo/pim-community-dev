<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command\OneTimeTask;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CleanProductScoresCommand extends Command
{
    use OneTimeTaskCommandTrait;

    protected static $defaultName = 'pim:data-quality-insights:clean-product-scores';
    protected static $defaultDescription = 'Clean product scores in order to have one score per product id.';

    private int $bulkSize = 1000;

    public function __construct(
        private Connection $dbConnection
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption('bulk-size', 's', InputOption::VALUE_REQUIRED, sprintf('Bulk size (%d by default)', $this->bulkSize));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (null !== $input->getOption('bulk-size')) {
            $this->bulkSize = \intval($input->getOption('bulk-size'));
            Assert::greaterThan($this->bulkSize, 0, 'Bulk size must be greater than zero.');
        }

        if (!$this->taskCanBeStarted(self::$defaultName)) {
            $output->writeln('This task has already been performed or is in progress.', OutputInterface::VERBOSITY_VERBOSE);
            return Command::SUCCESS;
        }

        $output->writeln('Start cleaning...');
        $this->startTask(self::$defaultName);

        $lastProductId = 0;

        while ($productScoresToClean = $this->getNextProductScoresToClean($lastProductId)) {
            try {
                $this->cleanProductScores($productScoresToClean);
            } catch (\Throwable $exception) {
                $this->deleteTask(self::$defaultName);
                throw $exception;
            }
        }

        $output->writeln('Cleaning done.');

        $this->finishTask(self::$defaultName);

        return Command::SUCCESS;
    }

    private function getNextProductScoresToClean(int $lastProductId): string
    {
        $query = <<<SQL
SELECT old_scores.product_id, old_scores.evaluated_at
FROM pim_data_quality_insights_product_score AS old_scores
INNER JOIN pim_data_quality_insights_product_score AS younger_scores
    ON younger_scores.product_id = old_scores.product_id
    AND younger_scores.evaluated_at > old_scores.evaluated_at
WHERE old_scores.product_id > :lastProductId
GROUP BY old_scores.product_id, old_scores.evaluated_at
ORDER BY old_scores.product_id ASC, old_scores.evaluated_at ASC
LIMIT :bulkSize;
SQL;

        $results = $this->dbConnection->executeQuery(
            $query,
            [
                'lastProductId' => $lastProductId,
                'bulkSize' => $this->bulkSize,
            ],
            [
                'lastProductId' => \PDO::PARAM_INT,
                'bulkSize' => \PDO::PARAM_INT,
            ]
        )->fetchAllAssociative();

        return implode(', ', array_map(function (array $productScoreToClean) {
            return sprintf(
                "%d",
                $productScoreToClean['product_id']
            );
        }, $results));
    }

    private function cleanProductScores(string $productIds): void
    {
        $this->dbConnection->executeQuery(
            <<<SQL
DELETE old_scores
FROM pim_data_quality_insights_product_score AS old_scores
INNER JOIN pim_data_quality_insights_product_score AS younger_scores
    ON younger_scores.product_id = old_scores.product_id
    AND younger_scores.evaluated_at > old_scores.evaluated_at
WHERE old_scores.product_id IN ($productIds);
SQL
        );
    }
}
