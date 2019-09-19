<?php
declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle\Command;

use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Bundle\Command\CreateUserCommand;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CreateUserCommandIntegration extends TestCase
{
    public function testCreateAdminUser()
    {
        $userData = [
            'admintest',
            'mypassword',
            'mypassword',
            'John',
            'Doe',
            'johndoe@email.com',
            'en_US',
            'en_US',
            'ecommerce',
            'master',
        ];

        $output = $this->executeCommand($userData);

        $createdUser = $this->getUserOr404($userData[0]);

        $this->assertEquals('admintest', $createdUser->getUsername());
        $this->assertContains(
            sprintf("User %s has been created.", $createdUser->getUsername()),
            $output
        );
    }

    public function testCannotCreateUsernameWithSpace()
    {
        $userData = [
            'invalid admin',
            'mypassword',
            'mypassword',
            'John',
            'Doe',
            'johndoe@email.com',
            'en_US',
            'en_US',
            'ecommerce',
            'master',
        ];

        try {
            $this->executeCommand($userData);
            $this->fail('InvalidArgumentException expected');
        } catch (Exception $exception) {
            $this->assertEquals('Aborted.', $exception->getMessage());
        }
    }

    public function testCannotCreateUsernameWithDifferentPasswords()
    {
        $userData = [
            'admin',
            'mypassword1',
            'mypassword2',
            'John',
            'Doe',
            'johndoe@email.com',
            'en_US',
            'en_US',
            'ecommerce',
            'master',
        ];

        try {
            $this->executeCommand($userData);
            $this->fail('InvalidArgumentException expected');
        } catch (Exception $exception) {
            $this->assertEquals('Aborted.', $exception->getMessage());
        }
    }

    public function testCannotCreateUsernameWithInvalidEmail()
    {
        $userData = [
            'admin',
            'mypassword',
            'mypassword',
            'John',
            'Doe',
            'bad_email.com',
            'en_US',
            'en_US',
            'ecommerce',
            'master',
        ];

        try {
            $this->executeCommand($userData);
            $this->fail('InvalidArgumentException expected');
        } catch (Exception $exception) {
            $this->assertEquals('Aborted.', $exception->getMessage());
        }
    }

    protected function executeCommand(array $userData): string
    {
        $this->resetShellVerbosity();

        $application = new Application(static::$kernel);
        $application->add(new CreateUserCommand());
        $command = $application->find(CreateUserCommand::COMMAND_NAME);
        $commandTester = new CommandTester($command);

        $commandTester->setInputs($userData);
        $commandTester->execute(['command' => $command->getName()]);

        return $commandTester->getDisplay();
    }

    private function getUserOr404(string $identifier): UserInterface
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($identifier);

        if (null === $user) {
            throw new NotFoundHttpException(
                sprintf('Username with id "%s" not found', $identifier)
            );
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * When running an application command, an environment variable is set with the verbosity level.
     * This environment variable is not reset when running another application command.
     *
     * With process isolation of phpunit deactivated, a test running an application command
     * impacts the next test, which will be executed in verbose mode also due to this stateful environment variable.
     *
     * This function resets the state.
     */
    private function resetShellVerbosity()
    {
        putenv('SHELL_VERBOSITY=0');
        $_ENV['SHELL_VERBOSITY'] = 0;
        $_SERVER['SHELL_VERBOSITY'] = 0;
    }
}
