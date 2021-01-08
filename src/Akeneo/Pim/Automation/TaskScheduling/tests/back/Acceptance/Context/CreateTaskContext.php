<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\TaskScheduling\Acceptance\Context;

use Akeneo\Pim\Automation\TaskScheduling\Application\Task\CreateTask\CreateTaskCommand;
use Akeneo\Pim\Automation\TaskScheduling\Application\Task\CreateTask\CreateTaskHandler;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\Task;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Repository\TaskRepository;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateTaskContext implements Context
{
    private CreateTaskHandler $createTaskHandler;
    private TaskRepository $taskRepository;

    public function __construct(CreateTaskHandler $createTaskHandler, TaskRepository $taskRepository)
    {
        $this->createTaskHandler = $createTaskHandler;
        $this->taskRepository = $taskRepository;
    }

    /**
     * @When I create a new task
     */
    public function iCreateANewTask(): void
    {
        try {
            $command = new CreateTaskCommand('code', 'command', '* * * * *', true);
            ($this->createTaskHandler)($command);
        } catch (\Throwable $e) {
            ExceptionContext::addException($e);
        }
    }

    /**
     * @Then the task with :code code should exist
     */
    public function theTaskShouldBeCreated(string $code): void
    {
        Assert::isInstanceOf($this->taskRepository->getByCode($code), Task::class);
    }
}
