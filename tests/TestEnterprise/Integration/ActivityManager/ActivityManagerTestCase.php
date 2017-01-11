<?php

namespace TestEnterprise\Integration\ActivityManager;

use Doctrine\DBAL\Connection;
use Pim\Behat\Context\DBALPurger;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use Test\Integration\TestCase;

class ActivityManagerTestCase extends TestCase
{
    /** {@inheritdoc} */
    protected $catalogName = 'activity_manager';

    /** {@inheritdoc} */
    protected $purgeDatabaseForEachTest = false;

    /**
     * {@inheritdoc}
     */
    protected function purgeDatabase()
    {
        $purger = new DBALPurger(
            $this->get('database_connection'),
            [
                'pimee_activity_manager_completeness_per_attribute_group',
                'pimee_activity_manager_project_product',
            ]
        );

        $purger->purge();

        parent::purgeDatabase();
    }

    /**
     * Create a project in database and run the project calculation
     *
     * @param array $projectData
     */
    protected function createProject(array $projectData)
    {
        $projectData = array_merge([
            'description' => 'An awesome description',
            'due_date' => '2020-01-19',
            'datagrid_view' => ['filters' => '', 'columns' => 'sku,label,family'],
        ], $projectData);

        $project = $this->get('pimee_activity_manager.factory.project')->create($projectData);
        $this->get('pimee_activity_manager.saver.project')->save($project);

        return $project;
    }

    /**
     * Run the project calculation
     *
     * @param ProjectInterface $project
     */
    protected function calculateProject(ProjectInterface $project)
    {
        $this->get('pimee_activity_manager.launcher.job.project_calculation')->launch($project);

        $this->isCompleteJobExecution(false);
    }
    /**
     * re run the project calculation
     *
     * @param ProjectInterface $project
     */
    protected function reCalculateProject(ProjectInterface $project)
    {
        $this->get('pimee_activity_manager.launcher.job.project_calculation')->launch($project);

        $this->isCompleteJobExecution(true);
    }

    /**
     * Return a DBAL connection
     *
     * @return Connection
     */
    protected function getConnection()
    {
        return $this->get('doctrine.orm.default_entity_manager')->getConnection();
    }

    /**
     * Check if the project calculation is complete before the timeout.
     *
     * @param bool $isRecalculation
     *
     * @return bool
     *
     * @throws \RuntimeException
     */
    private function isCompleteJobExecution($isRecalculation)
    {
        $sql = <<<SQL
SELECT count(`execution`.`id`)
FROM `akeneo_batch_job_execution` AS `execution`
LEFT JOIN `akeneo_batch_job_instance` AS `instance` ON `execution`.`job_instance_id` = `instance`.`id`
WHERE `instance`.`code` = :project_calculation 
AND `execution`.`exit_code` = 'COMPLETED'
SQL;

        $countOfJobExecution = $timeout = 0;
        while(!$isRecalculation && 1 !== $countOfJobExecution || $isRecalculation && 2 !== $countOfJobExecution ) {
            $countOfJobExecution = (int) $this->getConnection()->fetchColumn($sql, [
                'project_calculation' => $this->getParameter('pimee_activity_manager.project_calculation.job_name'),
            ]);

            if (50 === $timeout) {
                throw new \RuntimeException('The job does not finished before timeout');
            }

            $timeout++;
            sleep(1);
        }

        return true;
    }
}
