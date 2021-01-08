<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\TaskScheduling\Application\Task\CreateTask;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateTaskCommand
{
    private string $code;
    private string $command;
    private string $schedule;
    private bool $enabled;

    public function __construct(string $code, string $command, string $schedule, bool $enabled)
    {
        $this->code = $code;
        $this->command = $command;
        $this->schedule = $schedule;
        $this->enabled = $enabled;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function command(): string
    {
        return $this->command;
    }

    public function schedule(): string
    {
        return $this->schedule;
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }
}
