<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\CriteriaEvaluationRegistry;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InitializeProductsEvaluationsCommand extends Command
{
    private const BATCH_SIZE = 100;

    private Connection $dbConnection;

    private CriteriaEvaluationRegistry $productCriteriaRegistry;

    private CriteriaEvaluationRegistry $productModelCriteriaRegistry;

    public function __construct(
        Connection $dbConnection,
        CriteriaEvaluationRegistry $productCriteriaRegistry,
        CriteriaEvaluationRegistry $productModelCriteriaRegistry
    ) {
        parent::__construct();

        $this->dbConnection = $dbConnection;
        $this->productCriteriaRegistry = $productCriteriaRegistry;
        $this->productModelCriteriaRegistry = $productModelCriteriaRegistry;
    }

    protected function configure()
    {
        $this
            ->setName('pim:data-quality-insights:initialize-products-evaluations')
            ->setDescription('Initialize the evaluations of all the products and product models.')
            ->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Products evaluations initialization.');
        $io->caution([
            'All the products and product models will be marked to be (re)evaluated.',
            'This operation can take a lot of time depending on the number of products.'
        ]);

        if ($input->isInteractive()) {
            $confirm = $io->confirm('Are you sure you want to proceed ?', false);

            if ($confirm !== true) {
                $io->text('Operation aborted. Nothing has been done.');
                return 0;
            }
        }

        $this->initializeProducts($io);
        $this->initializeProductModels($io);

        return 0;
    }

    private function initializeProducts(SymfonyStyle $io): void
    {
        $io->text('Computing number of products to initialize...');

        $productCount = intval($this->dbConnection->executeQuery(<<<SQL
SELECT COUNT(*) FROM pim_catalog_product;
SQL
        )->fetchColumn());

        if ($productCount === 0) {
            $io->text('There are no products to initialize.');
            return;
        }

        $io->text(sprintf('Initialzing evaluation of %d products...', $productCount));

        $progressBar = new ProgressBar($io, $productCount);
        $progressBar->start();

        $criteria = array_map(fn ($criterionCode) => strval($criterionCode), $this->productCriteriaRegistry->getCriterionCodes());
        $statusPending = CriterionEvaluationStatus::PENDING;

        $lastProductId = 0;
        while ($productIds = $this->getProductIdsFrom($lastProductId)) {
            $values = implode(', ', $this->buildCriteriaEvaluationsValues($productIds, $criteria));
            $query = <<<SQL
INSERT INTO pim_data_quality_insights_product_criteria_evaluation (product_id, criterion_code, status) 
VALUES $values
ON DUPLICATE KEY UPDATE status = :statusPending;
SQL;
            $this->dbConnection->executeQuery($query, ['statusPending' => CriterionEvaluationStatus::PENDING]);

            $progressBar->advance(count($productIds));

            $lastProductId = intval(end($productIds));
        }

        $progressBar->clear();
        $io->success('All the products evaluations have been initialized.');
    }

    private function initializeProductModels(SymfonyStyle $io): void
    {
        $io->text('Computing number of product models to initialize...');

        $productModelCount = intval($this->dbConnection->executeQuery(<<<SQL
SELECT COUNT(*) FROM pim_catalog_product_model;
SQL
        )->fetchColumn());

        if ($productModelCount === 0) {
            $io->text('There are no product models to initialize.');
            return;
        }

        $io->text(sprintf('Initialzing evaluation of %d product models...', $productModelCount));

        $progressBar = new ProgressBar($io, $productModelCount);
        $progressBar->start();

        $criteria = array_map(fn ($criterionCode) => strval($criterionCode), $this->productModelCriteriaRegistry->getCriterionCodes());
        $statusPending = CriterionEvaluationStatus::PENDING;

        $lastProductModelId = 0;
        while ($productModelIds = $this->getProductModelIdsFrom($lastProductModelId)) {
            $values = implode(', ', $this->buildCriteriaEvaluationsValues($productModelIds, $criteria));
            $query = <<<SQL
INSERT INTO pim_data_quality_insights_product_model_criteria_evaluation (product_id, criterion_code, status) 
VALUES $values
ON DUPLICATE KEY UPDATE status = :statusPending;
SQL;
            $this->dbConnection->executeQuery($query, ['statusPending' => CriterionEvaluationStatus::PENDING]);

            $progressBar->advance(count($productModelIds));

            $lastProductModelId = intval(end($productModelIds));
        }

        $progressBar->clear();
        $io->success('All the product models evaluations have been initialized.');
    }

    private function buildCriteriaEvaluationsValues(array $productIds, array $criteria): array
    {
        $values = [];
        foreach ($productIds as $productId) {
            foreach ($criteria as $criterion) {
                $values[] = sprintf("(%d, '%s', '%s')", $productId, $criterion, CriterionEvaluationStatus::PENDING);
            }
        }

        return $values;
    }

    private function getProductIdsFrom(int $productId): array
    {
        $query = <<<SQL
SELECT id FROM pim_catalog_product WHERE id > :lastId ORDER BY id ASC LIMIT :limit;
SQL;

        return $this->dbConnection->executeQuery(
            $query,
            ['lastId' => $productId, 'limit' => self::BATCH_SIZE],
            ['lastId' => \PDO::PARAM_INT, 'limit' => \PDO::PARAM_INT]
        )->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function getProductModelIdsFrom(int $productModelId): array
    {
        $query = <<<SQL
SELECT id FROM pim_catalog_product_model WHERE id > :lastId ORDER BY id ASC LIMIT :limit;
SQL;

        return $this->dbConnection->executeQuery(
            $query,
            ['lastId' => $productModelId, 'limit' => self::BATCH_SIZE],
            ['lastId' => \PDO::PARAM_INT, 'limit' => \PDO::PARAM_INT]
        )->fetchAll(\PDO::FETCH_COLUMN);
    }
}
