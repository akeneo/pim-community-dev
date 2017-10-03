<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamworkAssistantBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Doctrine\DBAL\Connection;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectCompleteness;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;

class TeamworkAssistantTestCase extends TestCase
{
    /** @var JobLauncher */
    private $jobLauncher;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->jobLauncher = new JobLauncher(static::$kernel);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        $rootPath = $this->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        return new Configuration(
            [$rootPath . 'tests' . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'teamwork_assistant']
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
     * @throws \Exception
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

        $project = $this->get('pimee_teamwork_assistant.factory.project')->create($projectData);
        $violation = $this->get('validator')->validate($project);

        if (0 < count($violation)) {
            throw new \Exception('Project object is invalid');
        }

        $this->get('pimee_teamwork_assistant.saver.project')->save($project);

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

        $this->get('pimee_teamwork_assistant.launcher.job.project_calculation')->launch($project);

        $this->jobLauncher->launchConsumerOnce();

        $this->isCompleteJobExecution($numberOfExecutedJob);
    }


    /**
     * @param ProjectInterface $project
     */
    protected function removeProject(ProjectInterface $project)
    {
        $remover = $this->get('pimee_teamwork_assistant.remover.project');
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
        return $this->get('pimee_teamwork_assistant.repository.project_completeness')
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
            'project_calculation' => $this->getParameter('pimee_teamwork_assistant.project_calculation.job_name'),
        ]);
    }
}
