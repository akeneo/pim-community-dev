<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\TaskScheduling\back\Infrastructure\Persistence;

use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\Task;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskCode;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskId;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Repository\TaskNotFoundException;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Repository\TaskRepository;
use Akeneo\Pim\Automation\TaskScheduling\Infrastructure\Persistence\TaskRepositoryTrait;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbTaskRepository implements TaskRepository
{
    use TaskRepositoryTrait;

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function save(Task $task): void
    {
        // TODO
    }

    public function getById(TaskId $id): Task
    {
        throw TaskNotFoundException::withId($id);
    }

    public function getByCode(TaskCode $code): Task
    {
        throw TaskNotFoundException::withCode($code);
    }
}
