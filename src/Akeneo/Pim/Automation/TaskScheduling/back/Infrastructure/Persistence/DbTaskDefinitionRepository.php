<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\TaskScheduling\back\Infrastructure\Persistence;

use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskCode;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskDefinition;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition\TaskId;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Repository\TaskDefinitionRepository;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Repository\TaskNotFoundException;
use Akeneo\Pim\Automation\TaskScheduling\Infrastructure\Persistence\TaskDefinitionRepositoryTrait;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbTaskDefinitionRepository implements TaskDefinitionRepository
{
    use TaskDefinitionRepositoryTrait;

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function save(TaskDefinition $task): void
    {
        // TODO
    }

    public function getById(TaskId $id): TaskDefinition
    {
        throw TaskNotFoundException::withId($id);
    }

    public function getByCode(TaskCode $code): TaskDefinition
    {
        throw TaskNotFoundException::withCode($code);
    }
}
