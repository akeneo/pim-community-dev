<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\TaskScheduling\Domain\Repository;

use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskCode;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskId;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TaskNotFoundException extends \LogicException
{
    public static function withId(TaskId $taskId): TaskNotFoundException
    {
        $message = sprintf(
            'The task with \'%s\' id does not exist.',
            $taskId->asString()
        );

        return new self($message);
    }

    public static function withCode(TaskCode $code): TaskNotFoundException
    {
        $message = sprintf(
            'The task with \'%s\' code does not exist.',
            $code->asString()
        );

        return new self($message);
    }
}
