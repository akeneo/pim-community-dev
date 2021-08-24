<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InitializeJobs
{
    /** @var ObjectRepository */
    private $jobInstanceRepository;

    /** @var Connection */
    private $db;

    public function __construct(ObjectRepository $jobInstanceRepository, Connection $db)
    {
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->db = $db;
    }

    public function initialize(): void
    {
        if (!$this->isJobInstanceAlreadyCreated('data_quality_insights_evaluations')) {
            $this->createJobInstance('data_quality_insights_evaluations');
        }

        if (!$this->isJobInstanceAlreadyCreated('data_quality_insights_periodic_tasks')) {
            $this->createJobInstance('data_quality_insights_periodic_tasks');
        }

        if (!$this->isJobInstanceAlreadyCreated('data_quality_insights_prepare_evaluations')) {
            $this->createJobInstance('data_quality_insights_prepare_evaluations');
        }
    }

    private function createJobInstance(string $jobName): void
    {
        $query = <<<SQL
INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
VALUES (
    :job_name,
    :job_name,
    :job_name,
    0,
    'Data Quality Insights Connector',
    'a:0:{}',
    'data_quality_insights'
);
SQL;
        $this->db->executeUpdate(
            $query,
            [
                'job_name' => $jobName,
            ],
            [
                'job_name' => \PDO::PARAM_STR,
            ]
        );
    }

    private function isJobInstanceAlreadyCreated(string $code): bool
    {
        return null !== $this->jobInstanceRepository->findOneBy(['code' => $code]);
    }
}
