<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\TestEnterprise\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PimEnterprise\Bundle\InstallerBundle\Command\CleanCategoryAccessesCommand;
use PimEnterprise\Component\ActivityManager\Model\ProjectCompleteness;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ActivityManagerTestCase extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function doAfterFixtureImport(Application $application)
    {
        $extraCommand = $application->add(new CleanCategoryAccessesCommand());
        $extraCommand->setContainer($this->container);
        $command = new CommandTester($extraCommand);

        $exitCode = $command->execute([]);

        if (0 !== $exitCode) {
            throw new \Exception(sprintf('Catalog not installable! "%s"', $command->getDisplay()));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        $rootPath = $this->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        return new Configuration(
            [$rootPath . 'tests' . DIRECTORY_SEPARATOR . 'catalog' .    DIRECTORY_SEPARATOR . 'activity_manager'],
            false
        );
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
        $numberOfExecutedJob = $this->findJobExecutionCount();

        $this->get('pimee_activity_manager.launcher.job.project_calculation')->launch($project);

        $this->isCompleteJobExecution($numberOfExecutedJob);
    }

    /**
     * re run the project calculation
     *
     * @param ProjectInterface $project
     */
    protected function reCalculateProject(ProjectInterface $project)
    {
        $numberOfExecutedJob = $this->findJobExecutionCount();

        $this->get('pimee_activity_manager.launcher.job.project_calculation')->launch($project);

        $this->isCompleteJobExecution($numberOfExecutedJob);
    }

    /**
     * @param ProjectInterface $project
     */
    protected function removeProject(ProjectInterface $project)
    {
        $remover = $this->get('pimee_activity_manager.remover.project');
        $remover->remove($project);
    }

    /**
     * Get the project completeness
     *
     * @param ProjectInterface $project
     * @param string           $username
     *
     * @return ProjectCompleteness
     */
    protected function getProjectCompleteness(ProjectInterface $project, $username = null)
    {
        return $this->get('pimee_activity_manager.repository.project_completeness')
            ->getProjectCompleteness($project, $username);
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
     * @param int $numberOfExecutedJob
     *
     * @return bool
     *
     * @throws \RuntimeException
     */
    private function isCompleteJobExecution($numberOfExecutedJob)
    {
        $countOfJobExecution = $timeout = 0;
        while ($numberOfExecutedJob >= $countOfJobExecution) {
            $countOfJobExecution = $this->findJobExecutionCount();

            if (50 === $timeout) {
                throw new \RuntimeException('The job does not finished before timeout');
            }

            $timeout++;
            sleep(1);
        }

        return true;
    }

    /**
     * Find the number of execution for a project calculation job.
     *
     * @return int
     */
    private function findJobExecutionCount()
    {
        $sql = <<<SQL
SELECT count(`execution`.`id`)
FROM `akeneo_batch_job_execution` AS `execution`
LEFT JOIN `akeneo_batch_job_instance` AS `instance` ON `execution`.`job_instance_id` = `instance`.`id`
WHERE `instance`.`code` = :project_calculation
AND `execution`.`exit_code` = 'COMPLETED'
SQL;

        return (int)$this->getConnection()->fetchColumn($sql, [
            'project_calculation' => $this->getParameter('pimee_activity_manager.project_calculation.job_name'),
        ]);
    }
}
