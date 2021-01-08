<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\TaskScheduling\Domain\Model;

use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Task
{
    private TaskId $id;
    private string $code;
    private string $command;
    private string $schedule;
    private bool $enabled;

    private function __construct(
        TaskId $id,
        string $code,
        string $command,
        string $schedule,
        bool $enabled
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->command = $command;
        $this->schedule = $schedule;
        $this->enabled = $enabled;
    }

    public static function create(array $data): self
    {
        foreach (['id', 'code', 'command', 'schedule', 'enabled'] as $expectedKey) {
            Assert::keyExists($data, $expectedKey);
        }

        Assert::isInstanceOf($data['id'], TaskId::class);
        Assert::stringNotEmpty($data['code']);
        Assert::stringNotEmpty($data['command']);
        Assert::stringNotEmpty($data['schedule']);
        Assert::boolean($data['enabled']);

        return new self($data['id'], $data['code'], $data['command'], $data['schedule'], $data['enabled']);
    }

    public function id(): TaskId
    {
        return $this->id;
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
