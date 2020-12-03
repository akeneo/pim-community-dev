<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell\AspellDictionaryLocalFilesystemInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Doctrine\DBAL\Connection;
use League\Flysystem\MountManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class HealthCheckCommand extends Command
{
    private Connection $db;

    private FeatureFlag $featureFlag;

    private AspellDictionaryLocalFilesystemInterface $aspellDictionaryLocalFilesystem;

    private MountManager $mountManager;

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
        $this->outputMysqlInfo($io);
        $this->outputCriteriaInfo($io);
        $this->outputStructureSpellcheck($io);
        $this->outputDictionaryInfo($io);
        $this->outputPeriodicTasksInfo($io);
        $this->outputEvaluationJobInfo($io);

        if (count($productIds = $input->getOption('products')) > 0) {
            $this->outputLastEvaluationResultPerProducts($io, $productIds);
        }

        if (count($productModelsIds = $input->getOption('productModels')) > 0) {
            $this->outputLastEvaluationResultPerProductModels($io, $productModelsIds);
        }

        return 0;
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

        $stmt = $this->db->executeQuery(<<<SQL
SELECT count(*)
FROM pim_catalog_attribute
WHERE attribute_type = 'pim_catalog_simpleselect'
SQL
        );
        $attributesSimpleSelect = $stmt->fetchColumn();

        $stmt = $this->db->executeQuery(<<<SQL
SELECT count(*)
FROM pim_catalog_attribute
WHERE attribute_type = 'pim_catalog_multiselect'
SQL
        );
        $attributesMultiSelect = $stmt->fetchColumn();

        $stmt = $this->db->executeQuery(<<<SQL
SELECT count(*) FROM pim_catalog_attribute_option;
SQL
        );
        $attributeOptions = $stmt->fetchColumn();

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
                'number_of_attributes_simple_select' => $attributesSimpleSelect,
                'number_of_attributes_multi_select' => $attributesMultiSelect,
                'number_of_attribute_options' => $attributeOptions,
                'estimated_time_when_every_products_will_be_evaluated' => ($eta ? $eta->format(\DateTimeInterface::ATOM) : null)
            ]
        ];

        $io->horizontalTable(array_keys($data[0]), $data);
    }

    private function outputMysqlInfo(SymfonyStyle $io): void
    {
        $io->section('MySQL tables information');

        $query = <<<SQL
SELECT
  TABLE_NAME,
  TABLE_ROWS,
  AVG_ROW_LENGTH,
  ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS `Size (MB)`
FROM information_schema.TABLES
WHERE TABLE_NAME LIKE 'pimee_data_quality_insights_%' OR TABLE_NAME LIKE 'pimee_dqi_%';
SQL;

        $stmt = $this->db->executeQuery($query);

        $this->outputAsTable($io, $stmt->fetchAll());
    }

    private function outputCriteriaInfo(SymfonyStyle $io)
    {
        $this->outputEvaluationInfo($io);
    }

    private function outputEvaluationInfo(SymfonyStyle $io)
    {
        $io->section('Evaluation info');

        $io->comment('Number of products with criteria evaluated');
        $stmt = $this->db->executeQuery(<<<SQL
SELECT COUNT(DISTINCT product_id)
FROM pim_data_quality_insights_product_criteria_evaluation
SQL
        );
        $this->outputAsTable($io, $stmt->fetchAll());

        $io->comment('Number of product models with criteria evaluated');
        $stmt = $this->db->executeQuery(<<<SQL
SELECT COUNT(DISTINCT product_id)
FROM pim_data_quality_insights_product_model_criteria_evaluation
SQL
        );
        $this->outputAsTable($io, $stmt->fetchAll());

        $io->comment('Status of product criteria evaluations - total');
        $stmt = $this->db->executeQuery(<<<SQL
SELECT status, COUNT(status), MAX(evaluated_at)
FROM pim_data_quality_insights_product_criteria_evaluation
GROUP BY status
ORDER BY status
SQL
        );
        $this->outputAsTable($io, $stmt->fetchAll());

        $io->comment('Status of product model criteria evaluations - total');
        $stmt = $this->db->executeQuery(<<<SQL
SELECT status, COUNT(status), MAX(evaluated_at)
FROM pim_data_quality_insights_product_model_criteria_evaluation
GROUP BY status
ORDER BY status
SQL
        );
        $this->outputAsTable($io, $stmt->fetchAll());

        $io->comment('Product criteria on error with last error date');
        $stmt = $this->db->executeQuery(<<<SQL
SELECT status, criterion_code, COUNT(status), MAX(evaluated_at)
FROM pim_data_quality_insights_product_criteria_evaluation
WHERE status='error'
GROUP BY status, criterion_code
ORDER BY status
SQL
        );
        $this->outputAsTable($io, $stmt->fetchAll());

        $io->comment('Product models criteria on error with last error date');
        $stmt = $this->db->executeQuery(<<<SQL
SELECT status, criterion_code, COUNT(status), MAX(evaluated_at)
FROM pim_data_quality_insights_product_model_criteria_evaluation
WHERE status='error'
GROUP BY status, criterion_code
ORDER BY status
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
SELECT product_id, criterion_code, status, evaluated_at, result
FROM pim_data_quality_insights_product_criteria_evaluation
WHERE product_id = :product_id;
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
SELECT product_id, criterion_code, status, evaluated_at, result
FROM pim_data_quality_insights_product_model_criteria_evaluation
WHERE product_id = :product_id;
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
        $io->section('Product and product models evaluation jobs data');

        $query = <<<SQL
SELECT
    step.step_name AS task_name,
    AVG(TIMESTAMPDIFF(SECOND, step.start_time, step.end_time)) as average_execution_time_in_second,
    AVG(step.write_count) AS average_number_of_product_per_job
FROM akeneo_batch_job_execution AS job_execution
    JOIN akeneo_batch_step_execution AS step ON step.job_execution_id = job_execution.id
WHERE step.step_name IN ('evaluate_products_criteria', 'evaluate_product_models_criteria')
    AND job_execution.status = 1
    AND step.write_count > 0
GROUP BY step.step_name;
SQL;

        $stmt = $this->db->executeQuery($query);

        $this->outputAsTable($io, $stmt->fetchAll());

        $io->section('Attributes and options evaluation jobs');

        $query = <<<SQL
SELECT
    step.step_name AS task_name,
    AVG(TIMESTAMPDIFF(SECOND, step.start_time, step.end_time)) as average_execution_time_in_second
FROM akeneo_batch_job_execution AS job_execution
    JOIN akeneo_batch_step_execution AS step ON step.job_execution_id = job_execution.id
WHERE step.step_name IN ('evaluate_attributes', 'evaluate_attribute_options')
    AND job_execution.status = 1
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
FROM akeneo_batch_job_execution AS job_execution
    JOIN akeneo_batch_step_execution AS step ON step.job_execution_id = job_execution.id
WHERE step.step_name IN ('evaluate_products_criteria', 'evaluate_product_models_criteria')
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
FROM pim_data_quality_insights_product_criteria_evaluation
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

    private function outputStructureSpellcheck(SymfonyStyle $io): void
    {
        $io->section('Spellcheck on structure entities');

        $io->comment('Attributes spellcheck');
        $stmt = $this->db->executeQuery(<<<SQL
SELECT COUNT(*) AS total, SUM(to_improve) FROM pimee_dqi_attribute_spellcheck
SQL
        );
        $this->outputAsTable($io, $stmt->fetchAll());

        $io->comment('Attributes quality');
        $stmt = $this->db->executeQuery(<<<SQL
SELECT quality, COUNT(*) AS number_of_attributes
FROM pimee_dqi_attribute_quality GROUP BY quality;
SQL
        );
        $this->outputAsTable($io, $stmt->fetchAll());

        $io->comment('Attribute options spellcheck');
        $stmt = $this->db->executeQuery(<<<SQL
SELECT COUNT(*) AS total, SUM(to_improve)  FROM pimee_dqi_attribute_option_spellcheck
SQL
        );
        $this->outputAsTable($io, $stmt->fetchAll());
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
