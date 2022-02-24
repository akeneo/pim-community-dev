<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateProductModelScores;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\UpdateProductModelsIndex;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PopulateProductModelScoresCommand extends Command
{
    private const BULK_SIZE = 1000;
    protected static $defaultName = 'pim:data-quality-insights:populate-product-models-scores';

    public function __construct(
        private Connection $dbConnection,
        private ConsolidateProductModelScores $consolidateProductModelScores,
        private UpdateProductModelsIndex $updateProductModelsIndex,
    ) {
        parent::__construct();
    }

    protected function configure() :void
    {
        $this->setDescription('Populate scores for existing product models');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->commandCanBeStarted() && $output->isVerbose()) {
            $output->writeln('This process has already been performed or is in progress.');
            return Command::SUCCESS;
        }

        $output->writeln('process has started...');
        $this->persistCommandStart();

        $lastProductModelId = 0;

        while ($productModelIds = $this->getNextProductModelIds($lastProductModelId)) {
            try {
                $productModelIdsCollection = ProductIdCollection::fromInts($productModelIds);
                $this->consolidateProductModelScores->consolidate($productModelIdsCollection);
                $this->updateProductModelsIndex->execute($productModelIdsCollection);
                $lastProductModelId = end($productModelIds);
            } catch (\Throwable $e) {
                //Removes line in pim_one_time_task in order to be able to re-run the command if it previously failed
                $this->deleteTask();
                throw $e;
            }
        }

        $this->persistCommandDone();
        $output->writeln('process complete.');
        return Command::SUCCESS;
    }

    private function persistCommandStart(): void
    {
        $query = <<<SQL
INSERT IGNORE INTO pim_one_time_task (code, status, start_time) VALUES 
(:code, 'started', NOW());
SQL;

        $this->dbConnection->executeQuery($query, ['code' => self::$defaultName]);
    }

    /**
     * @throws Exception
     */
    private function persistCommandDone(): void
    {
        $query = <<<SQL
UPDATE pim_one_time_task 
SET status = 'done', end_time = NOW()
WHERE code = :code;
SQL;

        $this->dbConnection->executeQuery($query, ['code' => self::$defaultName]);
    }

    private function deleteTask(): void
    {
        $query = <<<SQL
DELETE
FROM pim_one_time_task 
WHERE code = :code;
SQL;
        $this->dbConnection->executeQuery($query, ['code' => self::$defaultName]);
    }

    private function commandCanBeStarted(): bool
    {
        $query = <<<SQL
SELECT 1 FROM pim_one_time_task WHERE code = :code
SQL;

        return !(bool)$this->dbConnection->executeQuery($query, ['code' => self::$defaultName])->fetchOne();
    }

    /**
     * @return int[]
     */
    private function getNextProductModelIds(int $lastProductModelId): array
    {
        $query = <<<SQL
SELECT product_model.id 
FROM pim_catalog_product_model AS product_model
WHERE product_model.id > :lastId
ORDER BY product_model.id
LIMIT :bulkSize;
SQL;

        $bulkResult = $this->dbConnection->executeQuery(
            $query,
            [
                'lastId' => $lastProductModelId,
                'bulkSize' => self::BULK_SIZE,
            ],
            [
                'lastId' => \PDO::PARAM_INT,
                'bulkSize' => \PDO::PARAM_INT,
            ]
        );

        return array_map(static function ($resultRow) {
            return (int)$resultRow['id'];
        }, $bulkResult->fetchAllAssociative());
    }
}
