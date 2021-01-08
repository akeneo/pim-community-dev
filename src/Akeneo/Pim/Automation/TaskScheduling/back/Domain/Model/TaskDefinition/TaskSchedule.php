<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\TaskScheduling\Domain\Model\TaskDefinition;

use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TaskSchedule
{
    private string $schedule;

    private function __construct(string $schedule)
    {
        $this->schedule = $schedule;
    }

    public static function fromString(string $schedule): self
    {
        Assert::stringNotEmpty($schedule, 'Schedule should not be empty');

        return new self($schedule);
    }

    public function asString(): string
    {
        return $this->schedule;
    }
}
