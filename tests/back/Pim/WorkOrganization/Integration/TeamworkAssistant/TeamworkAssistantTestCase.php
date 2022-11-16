<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectCompleteness;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Doctrine\DBAL\Connection;

class TeamworkAssistantTestCase extends TestCase
{
    private JobLauncher $jobLauncher;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        $rootPath = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR;

        return new Configuration(
            [
                $rootPath .
                'tests' .
                DIRECTORY_SEPARATOR .
                'back' .
                DIRECTORY_SEPARATOR .
                'Integration' .
                DIRECTORY_SEPARATOR .
                'catalog' .
                DIRECTORY_SEPARATOR .
                'teamwork_assistant',
            ],
            [],
            ['permission']
        );
    }

    /**
     * Create a project in database and run the project calculation
     */
    protected function createProject(
        string $label,
        string $owner,
        string $locale,
        string $channel,
        array $filters
    ): ProjectInterface {
        $dueDate = (new \DateTime())->modify('+1 month');
        $projectData = array_merge([
            'label' => $label,
            'locale' => $locale,
            'owner' => $owner,
            'channel' => $channel,
            'product_filters' => $filters,
            'description' => 'An awesome description',
            'due_date' => $dueDate->format('Y-m-d'),
            'datagrid_view' => ['filters' => '', 'columns' => 'sku,label,family'],
        ]);

        foreach ($projectData['product_filters'] as $key => $filter) {
            $projectData['product_filters'][$key] = array_merge($filter, [
                'context' => ['locale' => $projectData['locale'], 'scope' => $projectData['channel']],
            ]);
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

    protected function calculateProject(ProjectInterface $project): void
    {
        $numberOfExecutedJob = $this->findJobExecutionCount();

        $this->get('pimee_teamwork_assistant.launcher.job.project_calculation')->launch($project);

        $this->jobLauncher->launchConsumerOnce();

        $this->isCompleteJobExecution($numberOfExecutedJob);
    }

    protected function removeProject(ProjectInterface $project): void
    {
        // Reload the project to have it in Doctrine's unit of work
        $this->get('pimee_teamwork_assistant.remover.project')->remove(
            $this->get('pimee_teamwork_assistant.repository.project')->findOneByIdentifier(
                $project->getCode()
            )
        );
    }

    protected function getProjectCompleteness(ProjectInterface $project, ?string $username = null): ProjectCompleteness
    {
        return $this->get('pimee_teamwork_assistant.repository.project_completeness')
            ->getProjectCompleteness($project, $username);
    }

    protected function getConnection(): Connection
    {
        return $this->get('doctrine.orm.default_entity_manager')->getConnection();
    }

    /**
     * Check if the project calculation is complete before the timeout.
     *
     * @throws \RuntimeException
     */
    private function isCompleteJobExecution(int $numberOfExecutedJob): bool
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
     */
    private function findJobExecutionCount(): int
    {
        $sql = <<<SQL
        SELECT count(`execution`.`id`)
        FROM `akeneo_batch_job_execution` AS `execution`
        LEFT JOIN `akeneo_batch_job_instance` AS `instance` ON `execution`.`job_instance_id` = `instance`.`id`
        WHERE `instance`.`code` = :project_calculation
        AND `execution`.`exit_code` = 'COMPLETED'
        SQL;

        return (int) $this->getConnection()->fetchOne($sql, [
            'project_calculation' => $this->getParameter('pimee_teamwork_assistant.project_calculation.job_name'),
        ]);
    }
}
