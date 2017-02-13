<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PimEnterprise\Bundle\InstallerBundle\Command\CleanAttributeGroupAccessesCommand;
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
    public function setUp()
    {
        parent::setUp();

        $configuration = $this->getConfiguration();
        if ($configuration->isDatabasePurgedForEachTest() || 1 === self::$count) {
            $this->cleanRightAccesses();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function cleanRightAccesses()
    {
        $application = new Application();
        $cleanCategoryRight = $application->add(new CleanCategoryAccessesCommand());
        $cleanAttributeGroupRight = $application->add(new CleanAttributeGroupAccessesCommand());
        $cleanCategoryRight->setContainer(static::$kernel->getContainer());
        $cleanAttributeGroupRight->setContainer(static::$kernel->getContainer());
        $cleanCategoryRightCommand = new CommandTester($cleanCategoryRight);
        $cleanAttributeGroupRightCommand = new CommandTester($cleanAttributeGroupRight);

        $cleanCategoryRightCode = $cleanCategoryRightCommand->execute([]);
        $cleanAttributeGroupRightCode = $cleanAttributeGroupRightCommand->execute([]);

        if (0 !== $cleanCategoryRightCode && 0 !== $cleanAttributeGroupRightCode) {
            throw new \Exception('Failed to clean accesses.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getDatabasePurger()
    {
        return new DatabasePurger(static::$kernel->getContainer());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        $rootPath = $this->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        return new Configuration(
            [$rootPath . 'tests' . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'activity_manager'],
            false
        );
    }

    /**
     * Create a project in database and run the project calculation
     *
     * @param string $label
     * @param string $owner
     * @param string $locale
     * @param string $channel
     * @param array  $filters
     *
     * @return ProjectInterface
     */
    protected function createProject($label, $owner, $locale, $channel, array $filters)
    {
        $projectData = array_merge([
            'label'           => $label,
            'locale'          => $locale,
            'owner'           => $owner,
            'channel'         => $channel,
            'product_filters' => $filters,
            'description'     => 'An awesome description',
            'due_date'        => '2020-01-19',
            'datagrid_view'   => ['filters' => '', 'columns' => 'sku,label,family'],
        ]);

        if (isset($projectData['product_filters'])) {
            foreach ($projectData['product_filters'] as $key => $filter) {
                $projectData['product_filters'][$key] = array_merge($filter, [
                    'context'  => ['locale' => $projectData['locale'], 'scope' => $projectData['channel']],
                ]);
            }
        }

        $project = $this->get('pimee_activity_manager.factory.project')->create($projectData);
        $this->get('pimee_activity_manager.saver.project')->save($project);

        $this->calculateProject($project);

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
     * @throws \RuntimeException
     * @return bool
     *
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
