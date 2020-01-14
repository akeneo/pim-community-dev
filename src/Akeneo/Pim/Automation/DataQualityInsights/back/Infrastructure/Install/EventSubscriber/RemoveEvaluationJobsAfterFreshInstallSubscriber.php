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

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RemoveEvaluationJobsAfterFreshInstallSubscriber implements EventSubscriberInterface
{
    /** @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_LOAD_FIXTURES => 'removeEvaluationJobs',
        ];
    }

    public function removeEvaluationJobs(InstallerEvent $event): void
    {
        //Remove pending criteria for all products installed with fixtures
        $this->db->executeQuery(<<<SQL
TRUNCATE pimee_data_quality_insights_criteria_evaluation;
SQL
        );

        //Remove all 'data_quality_insights_evaluate_products_criteria' job execution queue rows
        $this->db->executeQuery(<<<SQL
DELETE akeneo_batch_job_execution_queue, akeneo_batch_job_execution
FROM akeneo_batch_job_execution_queue
LEFT JOIN akeneo_batch_job_execution ON (akeneo_batch_job_execution.id=akeneo_batch_job_execution_queue.job_execution_id)
LEFT JOIN akeneo_batch_job_instance ON (akeneo_batch_job_execution.job_instance_id=akeneo_batch_job_instance.id)
WHERE akeneo_batch_job_instance.code='data_quality_insights_evaluate_products_criteria';
SQL
        );

    }
}
