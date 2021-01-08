<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition;

use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TaskDefinition
{
    private TaskId $id;
    private TaskCode $code;
    private TaskCommand $command;
    private TaskSchedule $schedule;
    private bool $enabled;

    private function __construct(
        TaskId $id,
        TaskCode $code,
        TaskCommand $command,
        TaskSchedule $schedule,
        bool $enabled
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->command = $command;
        $this->schedule = $schedule;
        $this->enabled = $enabled;
    }

    /**
     * @param array<mixed> $data
     */
    public static function create(array $data): self
    {
        foreach (['id', 'code', 'command', 'schedule', 'enabled'] as $expectedKey) {
            Assert::keyExists($data, $expectedKey);
        }

        Assert::isInstanceOf($data['id'], TaskId::class);
        Assert::isInstanceOf($data['code'], TaskCode::class);
        Assert::isInstanceOf($data['command'], TaskCommand::class);
        Assert::isInstanceOf($data['schedule'], TaskSchedule::class);
        Assert::boolean($data['enabled']);

        return new self($data['id'], $data['code'], $data['command'], $data['schedule'], $data['enabled']);
    }

    public function id(): TaskId
    {
        return $this->id;
    }

    public function code(): TaskCode
    {
        return $this->code;
    }

    public function command(): TaskCommand
    {
        return $this->command;
    }

    public function schedule(): TaskSchedule
    {
        return $this->schedule;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function enable(): self
    {
        return new self(
            $this->id,
            $this->code,
            $this->command,
            $this->schedule,
            true
        );
    }

    public function disable(): self
    {
        return new self(
            $this->id,
            $this->code,
            $this->command,
            $this->schedule,
            false
        );
    }
}
