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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command\OneTimeTask;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CreateCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaByFeatureRegistry;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

final class InitializeGrowthEditionDoubleScoreCommand extends Command
{
    use OneTimeTaskCommandTrait;

    protected static $defaultName = 'pim:data-quality-insights:initialize-growth-edition-double-score';
    protected static $defaultDescription = 'Initialize DQI double score for the Growth editions.';

    private int $bulkSize = 100;

    public function __construct(
        private Connection $dbConnection,
        private CriteriaByFeatureRegistry $criteriaRegistry,
        private CreateCriteriaEvaluations $createCriteriaEvaluationsForProducts,
        private CreateCriteriaEvaluations $createCriteriaEvaluationsForProductModels,
        private ProductEntityIdFactoryInterface $productIdFactory,
        private ProductEntityIdFactoryInterface $productModelIdFactory,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption('bulk-size', 's', InputOption::VALUE_REQUIRED, sprintf('Bulk size (%d by default)', $this->bulkSize))
            ->setHidden(true);
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

        $criteriaToInitialize = array_diff($this->criteriaRegistry->getAllCriterionCodes(), $this->criteriaRegistry->getEnabledCriterionCodes());

        if (empty($criteriaToInitialize)) {
            $output->writeln('There are no criteria to initialize.');
            return Command::FAILURE;
        }

        $this->startTask(self::$defaultName);

        $output->writeln('Start initialization for products...');
        $this->initializeProducts($criteriaToInitialize);

        $output->writeln('Start initialization for product models...');
        $this->initializeProductModels($criteriaToInitialize);

        $output->writeln('Initialization done');
        $this->finishTask(self::$defaultName);

        return Command::SUCCESS;
    }

    private function initializeProducts(array $criteriaToInitialize): void
    {
        $lastProductId = '0';

        while ($productIds = $this->getNextBulkOfProductIds($lastProductId)) {
            try {
                $this->createCriteriaEvaluationsForProducts->create($criteriaToInitialize, $this->productIdFactory->createCollection($productIds));
                $lastProductId = end($productIds);
            } catch (\Throwable $exception) {
                $this->deleteTask(self::$defaultName);
                throw $exception;
            }
        }
    }

    private function getNextBulkOfProductIds(string $lastProductId): array
    {
        $limit = $this->bulkSize;

        $sql = <<<SQL
SELECT id FROM pim_catalog_product
WHERE id > :lastId
ORDER BY id ASC
LIMIT $limit;
SQL;

        return $this->dbConnection->executeQuery($sql, ['lastId' => $lastProductId])->fetchFirstColumn();
    }

    private function initializeProductModels(array $criteriaToInitialize): void
    {
        $lastProductModelId = '0';

        while ($productModelIds = $this->getNextBulkOfProductModelIds($lastProductModelId)) {
            try {
                $this->createCriteriaEvaluationsForProductModels->create($criteriaToInitialize, $this->productModelIdFactory->createCollection($productModelIds));
                $lastProductModelId = end($productModelIds);
            } catch (\Throwable $exception) {
                $this->deleteTask(self::$defaultName);
                throw $exception;
            }
        }
    }

    private function getNextBulkOfProductModelIds(string $lastProductModelId): array
    {
        $limit = $this->bulkSize;

        $sql = <<<SQL
SELECT id FROM pim_catalog_product_model
WHERE id > :lastId
ORDER BY id ASC
LIMIT $limit;
SQL;

        return $this->dbConnection->executeQuery($sql, ['lastId' => $lastProductModelId])->fetchFirstColumn();
    }
}
