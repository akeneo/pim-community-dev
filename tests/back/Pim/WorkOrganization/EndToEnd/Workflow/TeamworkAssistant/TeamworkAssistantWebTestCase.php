<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\WorkOrganization\EndToEnd\Workflow\TeamworkAssistant;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\UserRepository;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class TeamworkAssistantWebTestCase extends TestCase
{
    protected KernelBrowser $client;

    protected Connection $connection;

    protected JobLauncher $jobLauncher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::$container->get('test.client');
        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->connection = $this->get('database_connection');
    }

    protected function createProject($label, $owner, $locale, $channel, array $filters): ProjectInterface
    {
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

        if (isset($projectData['product_filters'])) {
            foreach ($projectData['product_filters'] as $key => $filter) {
                $projectData['product_filters'][$key] = array_merge($filter, [
                    'context' => ['locale' => $projectData['locale'], 'scope' => $projectData['channel']],
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

    protected function calculateProject(ProjectInterface $project): void
    {
        $numberOfExecutedJob = $this->findJobExecutionCount();

        $this->get('pimee_teamwork_assistant.launcher.job.project_calculation')->launch($project);

        $this->jobLauncher->launchConsumerOnce();

        $this->isCompleteJobExecution($numberOfExecutedJob);
    }

    /**
     * Check if the project calculation is complete before the timeout.
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
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        $rootPath = $this->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
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
                'teamwork_assistant'
            ]
        );
    }

    protected function authenticateAsAdmin(): UserInterface
    {
        /** @var UserRepository $userRepo */
        $userRepo = $this->get('pim_user.repository.user');
        $user = $userRepo->findOneByIdentifier('admin');

        $this->authenticate($user);

        return $user;
    }

    private function authenticate(UserInterface $user): void
    {
        $firewallName = 'main';
        $firewallContext = 'main';

        $token = new UsernamePasswordToken($user, null, $firewallName, $user->getRoles());
        $session = $this->getSession();
        $session->set('_security_' . $firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    private function getSession(): SessionInterface
    {
        return $this->client->getContainer()->get('session');
    }

    private function findJobExecutionCount(): int
    {
        $sql = <<<SQL
SELECT count(`execution`.`id`)
FROM `akeneo_batch_job_execution` AS `execution`
LEFT JOIN `akeneo_batch_job_instance` AS `instance` ON `execution`.`job_instance_id` = `instance`.`id`
WHERE `instance`.`code` = :project_calculation
AND `execution`.`exit_code` = 'COMPLETED'
SQL;

        return (int)$this->connection->fetchColumn($sql, [
            'project_calculation' => $this->getParameter('pimee_teamwork_assistant.project_calculation.job_name'),
        ]);
    }
}
