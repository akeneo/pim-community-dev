<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\Project;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Command\UpdateProjectOwnerCommand;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\TeamworkAssistantTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

class UpdateProjectOwnerCommandIntegration extends TeamworkAssistantTestCase
{
    private ?Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        $userRepository = $this->get('pim_user.repository.user');
        $projectRepository = $this->get('pimee_teamwork_assistant.repository.project');
        $projectUpdater = $this->get('pimee_teamwork_assistant.updater.project');
        $projectSaver = $this->get('pimee_teamwork_assistant.saver.project');
        $datagridViewUpdater = $this->get('pim_datagrid.updater.datagrid_view');
        $validator = $this->get('validator');

        $this->application = new Application($this->testKernel::class);
        $this->application->add(new UpdateProjectOwnerCommand(
            $userRepository,
            $projectRepository,
            $projectUpdater,
            $projectSaver,
            $datagridViewUpdater,
            $validator
        ));
    }

    public function testExecute(): void
    {
        $username = 'admin';
        $project = $this->createProject('Tshirt - ecommerce', 'Julia', 'en_US', 'ecommerce', [
            [
                'field' => 'family',
                'operator' => 'IN',
                'value' => ['tshirt'],
            ],
        ]);
        $command = $this->getCommand();

        $command->execute([
            'project' => $project->getId(),
            'owner' => $username
        ]);

        /** @var ProjectInterface $updatedProject */
        $updatedProject = $this
            ->get('pimee_teamwork_assistant.repository.project')
            ->findOneByIdentifier($project->getCode());

        $this->assertNewProjectOwnership($username, $updatedProject);
        $this->assertStringContainsString(
            sprintf(
                "%sThe project %d (%s) has been transferred to %s user.",
                PHP_EOL,
                $updatedProject->getId(),
                $updatedProject->getCode(),
                $username
            ),
            $command->getDisplay()
        );
        $command->assertCommandIsSuccessful();
    }

    public function testExecuteMissingArguments(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "project, owner").');
        $command = $this->getCommand();

        $command->execute([]);
    }

    public function testExecuteMissingProjectArgument(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "project").');
        $command = $this->getCommand();

        $command->execute(['owner' => 'username']);
    }

    public function testExecuteMissingOwnerArgument(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "owner").');
        $command = $this->getCommand();

        $command->execute(['project' => 1]);
    }

    public function testErrorMessageWhenProjectArgumentIsNotAnId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The project argument has to be an identifier.');

        $command = $this->getCommand();

        $command->execute([
            'project' => "ProjectId",
            'owner' => "Username"
        ]);

        $this->assertCommandFailed();
    }

    public function testErrorMessageWhenOwnerArgumentIsNotAString(): void
    {
        $project = $this->createProject('Tshirt - ecommerce', 'Julia', 'en_US', 'ecommerce', [
            [
                'field' => 'family',
                'operator' => 'IN',
                'value' => ['tshirt'],
            ],
        ]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The owner argument has to be a username.');

        $command = $this->getCommand();

        $command->execute([
            'project' => $project->getId(),
            'owner' => 1
        ]);
    }

    public function testProjectIsNotFound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The project is not found. Are your sure the project exists ?');
        $command = $this->getCommand();

        $command->execute(['project' => 1, 'owner' => 'username']);
    }

    public function testOwnerIsNotFound(): void
    {
        $project = $this->createProject('Tshirt - ecommerce', 'Julia', 'en_US', 'ecommerce', [
            [
                'field' => 'family',
                'operator' => 'IN',
                'value' => ['tshirt'],
            ],
        ]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The user is not found. Are your sure the user exists ?');
        $command = $this->getCommand();

        $command->execute(['project' => $project->getId(), 'owner' => 'username']);
    }

    private function getCommand(): CommandTester
    {
        $command = $this->application->find('pimee:project:update-owner');

        return new CommandTester($command);
    }

    private function assertCommandFailed(): void
    {
        Assert::assertEquals(Command::FAILURE, $this->getStatus());
    }

    private function assertNewProjectOwnership(string $expectedUsername, ProjectInterface $project)
    {
        $this->assertEquals($expectedUsername, $project->getOwner()->getUserIdentifier());
        $this->assertEquals($expectedUsername, $project->getDatagridView()->getOwner()->getUserIdentifier());
    }
}
