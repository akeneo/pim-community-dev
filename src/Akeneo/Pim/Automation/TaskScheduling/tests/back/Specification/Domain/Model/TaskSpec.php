<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\TaskScheduling\Domain\Model;

use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskCode;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskCommand;
use Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskId;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TaskSpec extends ObjectBehavior
{
    function it_can_be_enabled()
    {
        $this->beConstructedThrough('create', [[
            'id' => TaskId::fromString(Uuid::uuid4()->toString()),
            'code' => TaskCode::fromString('task_code'),
            'command' => TaskCommand::fromString('bin/console list'),
            'schedule' => '* * * * *',
            'enabled' => false,
        ]]);
        $this->isEnabled()->shouldBe(false);
        $task = $this->enable();
        $task->isEnabled()->shouldBe(true);
    }

    function it_can_be_disabled()
    {
        $this->beConstructedThrough('create', [[
            'id' => TaskId::fromString(Uuid::uuid4()->toString()),
            'code' => TaskCode::fromString('task_code'),
            'command' => TaskCommand::fromString('bin/console list'),
            'schedule' => '* * * * *',
            'enabled' => true,
        ]]);
        $this->isEnabled()->shouldBe(true);
        $task = $this->disable();
        $task->isEnabled()->shouldBe(false);
    }
}
