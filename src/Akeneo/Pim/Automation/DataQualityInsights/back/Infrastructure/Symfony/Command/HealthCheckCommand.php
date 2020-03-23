<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker\AspellDictionaryLocalFilesystemInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use League\Flysystem\MountManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class HealthCheckCommand extends Command
{
    /** @var Connection */
    private $db;

    /** @var FeatureFlag */
    private $featureFlag;

    /** @var AspellDictionaryLocalFilesystemInterface */
    private $aspellDictionaryLocalFilesystem;

    /** @var MountManager */
    private $mountManager;

    public function __construct(
        Connection $db,
        FeatureFlag $featureFlag,
        AspellDictionaryLocalFilesystemInterface $aspellDictionaryLocalFilesystem,
        MountManager $mountManager
    ) {
        $this->db = $db;
        $this->featureFlag = $featureFlag;
        $this->aspellDictionaryLocalFilesystem = $aspellDictionaryLocalFilesystem;
        $this->mountManager = $mountManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('pimee:data-quality-insights:health-check')
            ->addOption('products', 'p', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY)
            ->addOption('productModels', 'pm', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY)
            ->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('DQI health check command');

        $this->outputCatalogInfo($io);
        $this->outputCriteriaInfo($io);
        $this->outputDictionaryInfo($io);
        $this->outputPeriodicTasksInfo($io);
        $this->outputEvaluationJobInfo($io);

        if (count($productIds = $input->getOption('products')) > 0) {
            $this->outputTargetedProductsInfo($io, $productIds);
        }

        if (count($productModelsIds = $input->getOption('productModels')) > 0) {
            $this->outputTargetedProductModelsInfo($io, $productModelsIds);
        }
    }

    private function outputCatalogInfo(SymfonyStyle $io)
    {
        $io->section('General information');
        $stmt = $this->db->executeQuery(<<<SQL
SELECT count(*) AS number_of_products
FROM pim_catalog_product
WHERE product_model_id IS NULL
SQL
        );
        $products = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stmt = $this->db->executeQuery(<<<SQL
SELECT count(*) AS number_of_variant_products
FROM pim_catalog_product
WHERE product_model_id IS NOT NULL
SQL
        );
        $variant_products = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stmt = $this->db->executeQuery(<<<SQL
SELECT count(*) AS number_of_product_models
FROM pim_catalog_product_model
SQL
        );
        $product_models = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stmt = $this->db->executeQuery(<<<SQL
SELECT JSON_ARRAYAGG(code) as codes
FROM pim_catalog_locale
WHERE is_activated = 1
SQL
        );
        $locales = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stmt = $this->db->executeQuery(<<<SQL
SELECT JSON_ARRAYAGG(code) AS codes
FROM pim_catalog_channel
SQL
        );
        $channels = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stmt = $this->db->executeQuery(<<<SQL
SELECT count(*) AS number_of_families
FROM pim_catalog_family
SQL
        );
        $families = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stmt = $this->db->executeQuery(<<<SQL
SELECT count(*) AS number_of_categories
FROM pim_catalog_category
SQL
        );
        $categories = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stmt = $this->db->executeQuery(<<<SQL
SELECT count(*) AS number_of_attributes
FROM pim_catalog_attribute
WHERE attribute_type IN ('pim_catalog_text', 'pim_catalog_textarea');
SQL
        );
        $attributesTextAndTextarea = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stmt = $this->db->executeQuery(<<<SQL
SELECT count(*) AS number_of_attributes
FROM pim_catalog_attribute
WHERE attribute_type IN ('pim_catalog_text', 'pim_catalog_textarea')
AND is_scopable=1
AND is_localizable=0;
SQL
        );
        $attributesTextAndTextareaScopable = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stmt = $this->db->executeQuery(<<<SQL
SELECT count(*) AS number_of_attributes
FROM pim_catalog_attribute
WHERE attribute_type IN ('pim_catalog_text', 'pim_catalog_textarea')
AND is_scopable=0
AND is_localizable=1;
SQL
        );
        $attributesTextAndTextareaLocalizable = $stmt->fetch(\PDO::FETCH_ASSOC);

        $stmt = $this->db->executeQuery(<<<SQL
SELECT count(*) AS number_of_attributes
FROM pim_catalog_attribute
WHERE attribute_type IN ('pim_catalog_text', 'pim_catalog_textarea')
AND is_scopable=1
AND is_localizable=1;
SQL
        );
        $attributesTextAndTextareaScopableAndLocalizable = $stmt->fetch(\PDO::FETCH_ASSOC);

        $eta = $this->estimatedTimeOfArrivalForRemainingProducts();

        $data = [
            [
                'feature_activated' => $this->featureFlag->isEnabled() ? 1 : 0,
                'debug_date' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
                'number_of_products' => $products['number_of_products'],
                'number_of_variant_products' => $variant_products['number_of_variant_products'],
                'number_of_product_models' => $product_models['number_of_product_models'],
                'number_of_activated_locales' => count(json_decode($locales['codes'])),
                'activated_locales' => $locales['codes'],
                'number_of_channels' => count(json_decode($channels['codes'])),
                'channels' => $channels['codes'],
                'number_of_families' => $families['number_of_families'],
                'number_of_categories' => $categories['number_of_categories'],
                'number_of_attributes_text_and_textarea_total' => $attributesTextAndTextarea['number_of_attributes'],
                'number_of_attributes_text_and_textarea_scopable' => $attributesTextAndTextareaScopable['number_of_attributes'],
                'number_of_attributes_text_and_textarea_localizable' => $attributesTextAndTextareaLocalizable['number_of_attributes'],
                'number_of_attributes_text_and_textarea_scopable_and_localizable' => $attributesTextAndTextareaScopableAndLocalizable['number_of_attributes'],
                'estimated_time_when_every_products_will_be_evaluated' => ($eta ? $eta->format(\DateTimeInterface::ATOM) : null)
            ]
        ];

        $io->horizontalTable(array_keys($data[0]), $data);
    }

    private function outputCriteriaInfo(SymfonyStyle $io)
    {
        $this->outputEvaluationInfo($io);
        $this->outputAverageTimePerCriterion($io);
    }

    private function outputEvaluationInfo(SymfonyStyle $io)
    {
        $io->section('Evaluation info');

        $io->comment('Number of products with criteria evaluated');
        $stmt = $this->db->executeQuery(<<<SQL
SELECT COUNT(DISTINCT product_id)
FROM pimee_data_quality_insights_criteria_evaluation
SQL
        );

        $this->outputAsTable($io, $stmt->fetchAll());

        $io->comment('Status of criteria evaluation - total');
        $stmt = $this->db->executeQuery(<<<SQL
SELECT status, COUNT(status), MAX(ended_at)
FROM pimee_data_quality_insights_criteria_evaluation
GROUP BY status
ORDER BY status
SQL
        );

        $this->outputAsTable($io, $stmt->fetchAll());

        $io->comment('Criteria on error with last error date');
        $stmt = $this->db->executeQuery(<<<SQL
SELECT status, criterion_code, COUNT(status), MAX(started_at)
FROM pimee_data_quality_insights_criteria_evaluation
WHERE status='error'
GROUP BY status, criterion_code
ORDER BY status
SQL
        );

        $this->outputAsTable($io, $stmt->fetchAll());
    }

    private function outputAverageTimePerCriterion(SymfonyStyle $io)
    {
        $io->section('Average time per criterion');
        $stmt = $this->db->executeQuery(<<<SQL
SELECT
    criterion_code,
    AVG(TIMESTAMPDIFF(MICROSECOND , started_at, ended_at)) AS evaluation_time_in_microsecond,
    AVG(TIMESTAMPDIFF(SECOND , created_at, started_at)) AS handle_time_in_second
FROM pimee_data_quality_insights_criteria_evaluation
GROUP BY criterion_code
ORDER BY criterion_code
SQL
);

        $this->outputAsTable($io, $stmt->fetchAll());
    }

    private function outputDictionaryInfo(SymfonyStyle $io)
    {
        $this->outputGeneratedDictionaryInfo($io);
        $this->outputIgnoredWordInfo($io);
    }

    private function outputGeneratedDictionaryInfo(SymfonyStyle $io)
    {
        $io->section('Dictionaries generated on shared FS');

        $dictionaries = $this->mountManager->getFilesystem('dataQualityInsightsSharedAdapter')->listContents('/consistency', true);

        if (!empty($dictionaries)) {
            $dictionaries = array_filter($dictionaries, function ($path) {
                return $path['type'] !== 'dir';
            });
        }

        $this->outputAsTable($io, $dictionaries);

        $io->section('Dictionaries generated on local FS');

        $dictionaries = $this->aspellDictionaryLocalFilesystem->getFilesystem()->listContents('/consistency', true);

        if (!empty($dictionaries)) {
            $dictionaries = array_filter($dictionaries, function ($path) {
                return $path['type'] !== 'dir';
            });
        }

        $this->outputAsTable($io, $dictionaries);
    }

    private function outputIgnoredWordInfo(SymfonyStyle $io)
    {
        $io->section('Words ignored by users');
        $stmt = $this->db->executeQuery(<<<SQL
SELECT locale_code, JSON_ARRAYAGG(word) AS words
FROM pimee_data_quality_insights_text_checker_dictionary
GROUP BY locale_code
SQL
        );

        $ignored_words = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->outputAsTable($io, $ignored_words);
    }

    private function outputTargetedProductsInfo(SymfonyStyle $io, array $productIds)
    {
        $this->outputAverageTimePerCriterionPerProducts($io, $productIds);
        $this->outputProductAxisRatesPerProducts($io, $productIds);
        $this->outputLastEvaluationResultPerProducts($io, $productIds);
    }

    private function outputAverageTimePerCriterionPerProducts(SymfonyStyle $io, array $productIds)
    {
        $io->section('Average time per criterion per products');

        $query = <<<SQL
SELECT
    product_id,
    criterion_code,
    AVG(TIMESTAMPDIFF(MICROSECOND , started_at, ended_at)) AS evaluation_time_in_microsecond,
    AVG(TIMESTAMPDIFF(SECOND , created_at, started_at)) AS handle_time_in_second
FROM pimee_data_quality_insights_criteria_evaluation
WHERE product_id IN (:product_ids)
GROUP BY product_id, criterion_code
ORDER BY product_id, criterion_code
SQL;

        $stmt = $this->prepareStatementWithProductIds($query, $productIds);

        $this->outputAsTable($io, $stmt->fetchAll());
    }

    private function outputLastEvaluationResultPerProducts(SymfonyStyle $io, array $productIds)
    {
        $io->section('Last evaluation result per products');

        $productIds = array_map(function ($productId) {
            return intval($productId);
        },
            $productIds
        );

        foreach ($productIds as $productId) {
            $query = <<<SQL
SELECT
       latest_evaluation.product_id,
       latest_evaluation.criterion_code,
       latest_evaluation.status,
       latest_evaluation.result
FROM pimee_data_quality_insights_criteria_evaluation AS latest_evaluation
LEFT JOIN pimee_data_quality_insights_criteria_evaluation AS other_evaluation
    ON other_evaluation.product_id = :product_id
    AND latest_evaluation.criterion_code = other_evaluation.criterion_code
    AND latest_evaluation.created_at < other_evaluation.created_at
WHERE latest_evaluation.product_id = :product_id
    AND other_evaluation.id IS NULL;
SQL;
            $stmt = $this->db->executeQuery(
                $query,
                [
                    'product_id' => $productId
                ],
                [
                    'product_id' => \PDO::PARAM_INT
                ]
            );

            $data = $stmt->fetchAll();

            if (empty($data)) {
                $io->warning('No data found.');

                return;
            }

            $io->horizontalTable(array_keys(current($data)), $data);
        }
    }

    private function outputProductAxisRatesPerProducts(SymfonyStyle $io, array $productIds)
    {
        $io->section('Product Axis Rates per products');

        $query = <<<SQL
SELECT
    product_id, axis_code, evaluated_at, rates
FROM pimee_data_quality_insights_product_axis_rates
WHERE product_id IN (:product_ids)
ORDER BY product_id, axis_code
SQL;

        $stmt = $this->prepareStatementWithProductIds($query, $productIds);

        $this->outputAsTable($io, $stmt->fetchAll());
    }

    private function outputTargetedProductModelsInfo(SymfonyStyle $io, array $productModelsIds)
    {
        $this->outputAverageTimePerCriterionPerProductModels($io, $productModelsIds);
        $this->outputProductAxisRatesPerProductModels($io, $productModelsIds);
        $this->outputLastEvaluationResultPerProductModels($io, $productModelsIds);
    }

    private function outputAverageTimePerCriterionPerProductModels(SymfonyStyle $io, array $productModelsIds)
    {
        $io->section('Average time per criterion per product models');

        $query = <<<SQL
SELECT
    product_id,
    criterion_code,
    AVG(TIMESTAMPDIFF(MICROSECOND , started_at, ended_at)) AS evaluation_time_in_microsecond,
    AVG(TIMESTAMPDIFF(SECOND , created_at, started_at)) AS handle_time_in_second
FROM pimee_data_quality_insights_product_model_criteria_evaluation
WHERE product_id IN (:product_ids)
GROUP BY product_id, criterion_code
ORDER BY product_id, criterion_code
SQL;

        $stmt = $this->prepareStatementWithProductIds($query, $productModelsIds);

        $this->outputAsTable($io, $stmt->fetchAll());
    }

    private function outputProductAxisRatesPerProductModels(SymfonyStyle $io, array $productModelIds)
    {
        $io->section('Product Axis Rates per product models');

        $query = <<<SQL
SELECT
    product_id, axis_code, evaluated_at, rates
FROM pimee_data_quality_insights_product_model_axis_rates
WHERE product_id IN (:product_ids)
ORDER BY product_id, axis_code
SQL;

        $stmt = $this->prepareStatementWithProductIds($query, $productModelIds);
        $this->outputAsTable($io, $stmt->fetchAll());
    }

    private function outputLastEvaluationResultPerProductModels(SymfonyStyle $io, array $productIds)
    {
        $io->section('Last evaluation result per product models');

        $productIds = array_map(function ($productId) {
            return intval($productId);
        },
            $productIds
        );

        foreach ($productIds as $productId) {
            $query = <<<SQL
SELECT
       latest_evaluation.product_id,
       latest_evaluation.criterion_code,
       latest_evaluation.status,
       latest_evaluation.result
FROM pimee_data_quality_insights_product_model_criteria_evaluation AS latest_evaluation
LEFT JOIN pimee_data_quality_insights_product_model_criteria_evaluation AS other_evaluation
    ON other_evaluation.product_id = :product_id
    AND latest_evaluation.criterion_code = other_evaluation.criterion_code
    AND latest_evaluation.created_at < other_evaluation.created_at
WHERE latest_evaluation.product_id = :product_id
    AND other_evaluation.id IS NULL;
SQL;
            $stmt = $this->db->executeQuery($query, ['product_id' => $productId], ['product_id' => \PDO::PARAM_INT]);

            $data = $stmt->fetchAll();

            if (empty($data)) {
                $io->warning('No data found.');

                return;
            }

            $io->horizontalTable(array_keys(current($data)), $data);
        }
    }

    private function outputPeriodicTasksInfo(SymfonyStyle $io)
    {
        $io->section('Average time for each periodic tasks');

        $query = <<<SQL
SELECT
    step.step_name AS task_name,
    AVG(TIMESTAMPDIFF(SECOND, step.start_time, step.end_time)) as execution_time_in_second
FROM akeneo_batch_job_instance AS job
    JOIN akeneo_batch_job_execution AS job_execution ON job_execution.job_instance_id = job.id
    JOIN akeneo_batch_step_execution AS step ON step.job_execution_id = job_execution.id
WHERE job.code = 'data_quality_insights_periodic_tasks'
    AND job_execution.status = 1
GROUP BY step.step_name;
SQL;

        $stmt = $this->db->executeQuery($query);

        $this->outputAsTable($io, $stmt->fetchAll());
    }

    private function outputEvaluationJobInfo(SymfonyStyle $io)
    {
        $io->section('Evaluation jobs data');

        $query = <<<SQL
SELECT
    step.step_name AS task_name,
    AVG(TIMESTAMPDIFF(SECOND, step.start_time, step.end_time)) as average_execution_time_in_second,
    AVG(step.write_count) AS average_number_of_product_per_job,
    MAX(step.write_count) AS max_number_of_product_in_a_job
FROM akeneo_batch_job_instance AS job
    JOIN akeneo_batch_job_execution AS job_execution ON job_execution.job_instance_id = job.id
    JOIN akeneo_batch_step_execution AS step ON step.job_execution_id = job_execution.id
WHERE job.code = 'data_quality_insights_evaluate_products_criteria'
    AND job_execution.status = 1
    AND step.write_count > 0
GROUP BY step.step_name;
SQL;

        $stmt = $this->db->executeQuery($query);

        $this->outputAsTable($io, $stmt->fetchAll());
    }

    private function estimatedTimeOfArrivalForRemainingProducts(): ?\DateTimeImmutable
    {
        $query = <<<SQL
SELECT
    step.step_name AS task_name,
    AVG(TIMESTAMPDIFF(SECOND, step.start_time, step.end_time)) as average_execution_time_in_second,
    AVG(step.write_count) AS average_number_of_product_per_job
FROM akeneo_batch_job_instance AS job
    JOIN akeneo_batch_job_execution AS job_execution ON job_execution.job_instance_id = job.id
    JOIN akeneo_batch_step_execution AS step ON step.job_execution_id = job_execution.id
WHERE job.code = 'data_quality_insights_evaluate_products_criteria'
    AND job_execution.status = 1
    AND step.write_count > 0
GROUP BY step.step_name;
SQL;

        $stmt = $this->db->executeQuery($query);

        $result = $stmt->fetch();

        $meanTimeOfEvaluationPerProductInSeconds = 0;

        if (!empty($result) && $result['average_number_of_product_per_job'] > 0) {
            $meanTimeOfEvaluationPerProductInSeconds = $result['average_execution_time_in_second'] / $result['average_number_of_product_per_job'];
        }

        $stmt = $this->db->executeQuery(<<<SQL
SELECT COUNT(DISTINCT product_id) as number_of_product_to_evaluate
FROM pimee_data_quality_insights_criteria_evaluation
WHERE status = 'pending'
SQL
        );

        $result = $stmt->fetch();

        $delay = 0;

        if (!empty($result)) {
            $delay = $result['number_of_product_to_evaluate'] * $meanTimeOfEvaluationPerProductInSeconds;
        }

        if ($delay !== 0) {
            $now = new \DateTimeImmutable();
            $estimatedTimeOfArrival = $now->modify(sprintf('+%d sec', $delay));

            return $estimatedTimeOfArrival;
        }

        return null;
    }

    private function prepareStatementWithProductIds(string $query, array $productIds): ResultStatement
    {
        $productIds = array_map(function ($productId) {
            return intval($productId);
        },
            $productIds
        );

        return $this->db->executeQuery(
            $query,
            [
                'product_ids' => $productIds
            ],
            [
                'product_ids' => Connection::PARAM_INT_ARRAY
            ]
        );
    }

    private function outputAsTable(SymfonyStyle $io, array $data)
    {
        if (empty($data)) {
            $io->warning('No data found.');

            return;
        }

        $io->table(array_keys(current($data)), $data);
    }
}
