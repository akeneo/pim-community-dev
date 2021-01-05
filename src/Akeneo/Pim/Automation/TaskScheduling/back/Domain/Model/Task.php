<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\TaskScheduling\Domain\Model;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Task
{
    private string $code;
    private string $command;
    private string $schedule;
    private bool $enabled;

    public function __construct(
        string $code,
        string $command,
        string $schedule,
        bool $enabled
    ) {
        $this->code = $code;
        $this->command = $command;
        $this->schedule = $schedule;
        $this->enabled = $enabled;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }
}
