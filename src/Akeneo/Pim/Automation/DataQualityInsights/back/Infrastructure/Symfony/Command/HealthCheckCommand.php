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

        if (count($productIds = $input->getOption('products')) > 0) {
            $this->outputTargetedProductsInfo($io, $productIds);
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

        $data = [
            [
                'feature_activated' => $this->featureFlag->isEnabled() ? 1 : 0,
                'debug_date' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
                'number_of_products' => $products['number_of_products'],
                'number_of_activated_locales' => count(json_decode($locales['codes'])),
                'activated_locales' => $locales['codes'],
                'number_of_activated_channels' => count(json_decode($channels['codes'])),
                'channels' => $channels['codes'],
                'number_of_families' => $families['number_of_families'],
                'number_of_categories' => $categories['number_of_categories']
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

        $dictionaries = $this->mountManager->getFilesystem('dataQualityInsightsSharedAdapter')->listContents('/', true);

        if (!empty($dictionaries)) {
            $dictionaries = array_filter($dictionaries, function ($path) {
                return $path['type'] !== 'dir';
            });
        }

        $this->outputAsTable($io, $dictionaries);

        $io->section('Dictionaries generated on local FS');

        $dictionaries = $this->aspellDictionaryLocalFilesystem->getFilesystem()->listContents('/', true);

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
