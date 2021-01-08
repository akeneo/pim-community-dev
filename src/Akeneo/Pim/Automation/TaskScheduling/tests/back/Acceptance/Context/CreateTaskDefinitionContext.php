<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\TaskScheduling\Acceptance\Context;

use Akeneo\Pim\Automation\TaskScheduling\Application\TaskDefinition\Create\CreateTaskDefinitionCommand;
use Akeneo\Pim\Automation\TaskScheduling\Application\TaskDefinition\Create\CreateTaskDefinitionHandler;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskCode;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskDefinition;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Repository\TaskDefinitionRepository;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateTaskDefinitionContext implements Context
{
    private CreateTaskDefinitionHandler $createTaskDefinitionHandler;
    private TaskDefinitionRepository $taskDefinitionRepository;

    public function __construct(
        CreateTaskDefinitionHandler $createTaskDefinitionHandler,
        TaskDefinitionRepository $taskDefinitionRepository
    ) {
        $this->createTaskDefinitionHandler = $createTaskDefinitionHandler;
        $this->taskDefinitionRepository = $taskDefinitionRepository;
    }

    /**
     * @When I create a new task definition with the :code code
     */
    public function iCreateANewTaskDefinition(string $code): void
    {
        try {
            $command = new CreateTaskDefinitionCommand($code, 'command', '* * * * *', true);
            ($this->createTaskDefinitionHandler)($command);
        } catch (\Throwable $e) {
            ExceptionContext::addException($e);
        }
    }

    /**
     * @Then the task definition with :code code should exist
     */
    public function theTaskDefinitionShouldExist(string $code): void
    {
        Assert::isInstanceOf($this->taskDefinitionRepository->getByCode(TaskCode::fromString($code)), TaskDefinition::class);
    }
}
