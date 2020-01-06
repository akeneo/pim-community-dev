<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install\EventSubscriber;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\Tasklet\EvaluateProductsCriteriaTasklet;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InitDataQualityInsightsJobsSubscriber implements EventSubscriberInterface
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

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::PRE_LOAD_FIXTURES => 'initJobs',
        ];
    }

    public function initJobs(InstallerEvent $event): void
    {
        if (!$this->isJobInstanceAlreadyCreated(EvaluateProductsCriteriaTasklet::JOB_INSTANCE_NAME)) {
            $this->createJobInstance(EvaluateProductsCriteriaTasklet::JOB_INSTANCE_NAME);
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
