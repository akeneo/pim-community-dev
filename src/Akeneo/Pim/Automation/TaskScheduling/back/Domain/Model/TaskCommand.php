<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\TaskScheduling\Domain\Model;

use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TaskCommand
{
    private string $command;

    private function __construct(string $command)
    {
        $this->command = $command;
    }

    public static function fromString(string $command): self
    {
        Assert::stringNotEmpty($command, 'Command should not be empty');

        return new self($command);
    }

    public function asString(): string
    {
        return $this->command;
    }

    public function equals(TaskCommand $otherCommand): bool
    {
        return $this->command === $otherCommand->asString();
    }
}
