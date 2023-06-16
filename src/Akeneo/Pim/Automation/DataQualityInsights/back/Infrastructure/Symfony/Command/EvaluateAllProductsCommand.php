<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProductModels;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateProducts;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetEntityIdsToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductModelIdsToEvaluateQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductUuidsToEvaluateQuery;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EvaluateAllProductsCommand extends Command
{
    private const LIMIT_PER_LOOP = 1000;
    private const DEFAULT_BULK_SIZE = 100;

    protected static $defaultName = 'pim:data-quality-insights:evaluate-all-products';
    protected static $defaultDescription = 'Evaluate all products and product models having pending criteria.';

    public function __construct(
        private Connection                        $dbConnection,
        private GetProductUuidsToEvaluateQuery    $getProductIdsToEvaluateQuery,
        private GetProductModelIdsToEvaluateQuery $getProductModelsIdsToEvaluateQuery,
        private EvaluateProducts                  $evaluateProducts,
        private EvaluateProductModels             $evaluateProductModels
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setHidden(true);
        $this->addOption('bulk-size', null, InputOption::VALUE_REQUIRED, sprintf('Bulk size (%d by default)', self::DEFAULT_BULK_SIZE));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title($this::$defaultDescription);
        $io->text('Use command pim:data-quality-insights:initialize-products-evaluations first if you want to re-evaluate the totality of the catalog.');
        $io->caution(['This operation can take a lot of time.']);

        if ($input->isInteractive()) {
            $confirm = $io->confirm('Are you sure you want to proceed ?', false);

            if ($confirm !== true) {
                $io->text('Operation aborted. Nothing has been done.');
                return Command::SUCCESS;
            }
        }

        $bulkSize = $input->getOption('bulk-size') ?? self::DEFAULT_BULK_SIZE;

        $this->evaluateAllProducts($io, intval($bulkSize));
        $this->evaluateAllProductModels($io, intval($bulkSize));

        return Command::SUCCESS;
    }

    private function evaluateAllProducts(SymfonyStyle $io, int $bulkSize): void
    {
        $io->text('Evaluating products...');

        $countProductsToEvaluate = $this->countProductsToEvaluate();
        if (0 === $countProductsToEvaluate) {
            $io->text('There are no products to evaluate.');
            return;
        }

        $progressBar = new ProgressBar($io, $countProductsToEvaluate);
        $progressBar->start();
        $totalEvaluations = 0;

        do {
            $evaluationCount = 0;
            foreach ($this->getProductIdsToEvaluateQuery->execute(self::LIMIT_PER_LOOP, $bulkSize) as $productIds) {
                $this->evaluateProducts->forPendingCriteria($productIds);

                $countProductIds = count($productIds);
                $progressBar->advance($countProductIds);
                $evaluationCount += $countProductIds;
                $totalEvaluations+= $countProductIds;
            }
        } while ($evaluationCount > 0);

        $progressBar->clear();
        $io->success(sprintf('%d products have been evaluated.', $totalEvaluations));
    }

    private function evaluateAllProductModels(SymfonyStyle $io, int $bulkSize): void
    {
        $io->text('Evaluating product models...');

        $countProductModelsToEvaluate = $this->countProductModelsToEvaluate();
        if (0 === $countProductModelsToEvaluate) {
            $io->text('There are no product models to evaluate.');
            return;
        }

        $progressBar = new ProgressBar($io, $countProductModelsToEvaluate);
        $progressBar->start();
        $totalEvaluations = 0;

        do {
            $evaluationCount = 0;
            foreach ($this->getProductModelsIdsToEvaluateQuery->execute(self::LIMIT_PER_LOOP, $bulkSize) as $productModelIds) {
                $this->evaluateProductModels->forPendingCriteria($productModelIds);

                $countProductIds = count($productModelIds);
                $progressBar->advance($countProductIds);
                $evaluationCount += $countProductIds;
                $totalEvaluations+= $countProductIds;
            }
        } while ($evaluationCount > 0);

        $progressBar->clear();
        $io->success(sprintf('%d product models have been evaluated.', $totalEvaluations));
    }

    private function countProductsToEvaluate(): int
    {
        $query = <<<SQL
SELECT COUNT(DISTINCT product_uuid) 
FROM pim_data_quality_insights_product_criteria_evaluation
WHERE status = 'pending';
SQL;

        return intval($this->dbConnection->executeQuery($query)->fetchOne());
    }

    private function countProductModelsToEvaluate(): int
    {
        $query = <<<SQL
SELECT COUNT(DISTINCT product_id) 
FROM pim_data_quality_insights_product_model_criteria_evaluation
WHERE status = 'pending';
SQL;

        return intval($this->dbConnection->executeQuery($query)->fetchOne());
    }
}
