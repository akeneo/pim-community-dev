<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command\OneTimeTask;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultCodes;
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
        foreach ($this->getBulksOfCriterionResultsToClean('pim_data_quality_insights_product_criteria_evaluation') as $resultsBulk) {
            $cleanedResults = $this->cleanBulkOfCriterionResults($resultsBulk);
            $this->saveBulkOfCleanedResults('pim_data_quality_insights_product_criteria_evaluation', $cleanedResults);
        }
    }

    private function cleanEvaluationResultsForProductModels(): void
    {
        foreach ($this->getBulksOfCriterionResultsToClean('pim_data_quality_insights_product_model_criteria_evaluation') as $resultsBulk) {
            $cleanedResults = $this->cleanBulkOfCriterionResults($resultsBulk);
            $this->saveBulkOfCleanedResults('pim_data_quality_insights_product_model_criteria_evaluation', $cleanedResults);
        }
    }

    private function getBulksOfCriterionResultsToClean(string $tableName): \Generator
    {
        $limit = $this->bulkSize;

        $query = <<<SQL
SELECT product_id, criterion_code, status ,result
FROM $tableName
WHERE product_id > :lastProductId
    AND criterion_code IN (:criterionCodes)
ORDER BY product_id ASC
LIMIT $limit;
SQL;

        $lastProductId = 0;

        do {
            $results = $this->dbConnection->executeQuery(
                $query,
                [
                    'lastProductId' => $lastProductId,
                    'criterionCodes' => [
                        EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE,
                        EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE,
                    ]
                ],
                [
                    'lastProductId' => \PDO::PARAM_INT,
                    'criterionCodes' => Connection::PARAM_STR_ARRAY,
                ]
            )->fetchAllAssociative();

            if (!empty($results)) {
                $lastProductId = end($results)['product_id'];
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

    private function saveBulkOfCleanedResults(string $tableName, array $cleanedResults): void
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
INSERT INTO $tableName (product_id, criterion_code, status, result) 
VALUES $values AS cleaned_values
ON DUPLICATE KEY UPDATE result = cleaned_values.result;
SQL;

        $this->dbConnection->executeQuery($query);
    }
}
