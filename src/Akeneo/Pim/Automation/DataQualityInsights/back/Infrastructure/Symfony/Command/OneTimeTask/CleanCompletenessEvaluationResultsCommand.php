<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command\OneTimeTask;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultCodes;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CleanCompletenessEvaluationResultsCommand extends Command
{
    use OneTimeTaskCommandTrait;

    protected static $defaultName = 'pim:data-quality-insights:clean-completeness-evaluation-results';
    protected static $defaultDescription = 'Clean the results of the products completeness criteria to replace the list of attributes codes by their number.';

    private int $bulkSize = 200;

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

        try {
            $this->cleanEvaluationResultsForProducts();
            $this->cleanEvaluationResultsForProductModels();
        } catch (\Throwable $exception) {
            $this->deleteTask(self::$defaultName);
            throw $exception;
        }

        $output->writeln('Cleaning done.');

        $this->finishTask(self::$defaultName);

        return Command::SUCCESS;
    }

    private function cleanEvaluationResultsForProducts(): void
    {
        foreach ($this->getBulksOfProductCriterionResultsToClean() as $resultsBulk) {
            $cleanedResults = $this->cleanBulkOfCriterionResults($resultsBulk);
            $this->saveBulkOfProductCleanedResults($cleanedResults);
        }
    }

    private function cleanEvaluationResultsForProductModels(): void
    {
        foreach ($this->getBulksOfProductModelCriterionResultsToClean() as $resultsBulk) {
            $cleanedResults = $this->cleanBulkOfCriterionResults($resultsBulk);
            $this->saveBulkOfProductModelCleanedResults($cleanedResults);
        }
    }

    private function getBulksOfProductCriterionResultsToClean(): \Generator
    {
        $limit = $this->bulkSize;

        $query = <<<SQL
SELECT 
    /*+ SET_VAR(sort_buffer_size = 1000000) */
    BIN_TO_UUID(product_uuid) AS product_uuid, 
    criterion_code, 
    status, 
    result
FROM pim_data_quality_insights_product_criteria_evaluation
WHERE product_uuid > :lastProductUuidAsBytes
    AND criterion_code IN (:criterionCodes)
ORDER BY product_uuid ASC
LIMIT $limit;
SQL;

        $lastProductUuidAsBytes = '';

        do {
            $results = $this->dbConnection->executeQuery(
                $query,
                [
                    'lastProductUuidAsBytes' => $lastProductUuidAsBytes,
                    'criterionCodes' => [
                        EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE,
                        EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE,
                    ]
                ],
                [
                    'lastProductUuidAsBytes' => \PDO::PARAM_STR,
                    'criterionCodes' => Connection::PARAM_STR_ARRAY,
                ]
            )->fetchAllAssociative();

            if (!empty($results)) {
                $lastProductUuidAsBytes = Uuid::fromString(end($results)['product_uuid'])->getBytes();
                yield array_map(function (array $resultRow) {
                    $resultRow['result'] = null !== $resultRow['result'] ? \json_decode($resultRow['result'], true) : [];
                    return $resultRow;
                }, $results);
            }
        } while (!empty($results));
    }

    private function getBulksOfProductModelCriterionResultsToClean(): \Generator
    {
        $limit = $this->bulkSize;

        $query = <<<SQL
SELECT product_id, criterion_code, status, result
FROM pim_data_quality_insights_product_model_criteria_evaluation
WHERE product_id > :lastProductModelId
    AND criterion_code IN (:criterionCodes)
ORDER BY product_id ASC
LIMIT $limit;
SQL;

        $lastProductModelId = 0;

        do {
            $results = $this->dbConnection->executeQuery(
                $query,
                [
                    'lastProductModelId' => $lastProductModelId,
                    'criterionCodes' => [
                        EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE,
                        EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE,
                    ]
                ],
                [
                    'lastProductModelId' => \PDO::PARAM_INT,
                    'criterionCodes' => Connection::PARAM_STR_ARRAY,
                ]
            )->fetchAllAssociative();

            if (!empty($results)) {
                $lastProductModelId = end($results)['product_id'];
                yield array_map(function (array $resultRow) {
                    $resultRow['result'] = null !== $resultRow['result'] ? \json_decode($resultRow['result'], true) : [];
                    return $resultRow;
                }, $results);
            }
        } while (!empty($results));
    }

    private function cleanBulkOfCriterionResults(array $resultsBulk): array
    {
        $indexData = TransformCriterionEvaluationResultCodes::PROPERTIES_ID['data'];
        $indexAttributesList = TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['attributes_with_rates'];
        $indexAttributesCount = TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['number_of_improvable_attributes'];

        $cleanedResults = [];
        foreach ($resultsBulk as $resultRow) {
            if (isset($resultRow['result'][$indexData][$indexAttributesList])) {
                $resultRow['result'][$indexData][$indexAttributesCount] = $this->countAttributesByChannelAndLocales($resultRow['result'][$indexData][$indexAttributesList]);
                unset($resultRow['result'][$indexData][$indexAttributesList]);
                $cleanedResults[] = $resultRow;
            }
        }

        return $cleanedResults;
    }

    private function countAttributesByChannelAndLocales(array $attributesList): array
    {
        return array_map(
            fn ($attributesByLocale) => array_map(
                fn ($attributes) => count($attributes),
                $attributesByLocale
            ),
            $attributesList
        );
    }

    private function saveBulkOfProductCleanedResults(array $cleanedResults): void
    {
        if (empty($cleanedResults)) {
            return;
        }

        $values = implode(', ', array_map(function (array $result) {
            return sprintf(
                "(UUID_TO_BIN('%s'), '%s', '%s', '%s')",
                $result['product_uuid'],
                $result['criterion_code'],
                $result['status'],
                \json_encode($result['result'])
            );
        }, $cleanedResults));

        // To update data by bulk in a single query, it's easiest to do it with "INSERT INTO... ON DUPLICATE KEY UPDATE..."
        $query = <<<SQL
INSERT INTO pim_data_quality_insights_product_criteria_evaluation (product_uuid, criterion_code, status, result) 
VALUES $values AS cleaned_values
ON DUPLICATE KEY UPDATE result = cleaned_values.result;
SQL;

        $this->dbConnection->executeQuery($query);
    }

    private function saveBulkOfProductModelCleanedResults(array $cleanedResults): void
    {
        if (empty($cleanedResults)) {
            return;
        }

        $values = implode(', ', array_map(function (array $result) {
            return sprintf(
                "(%d, '%s', '%s', '%s')",
                $result['product_id'],
                $result['criterion_code'],
                $result['status'],
                \json_encode($result['result'])
            );
        }, $cleanedResults));

        // To update data by bulk in a single query, it's easiest to do it with "INSERT INTO... ON DUPLICATE KEY UPDATE..."
        $query = <<<SQL
INSERT INTO pim_data_quality_insights_product_model_criteria_evaluation (product_id, criterion_code, status, result) 
VALUES $values AS cleaned_values
ON DUPLICATE KEY UPDATE result = cleaned_values.result;
SQL;

        $this->dbConnection->executeQuery($query);
    }
}
