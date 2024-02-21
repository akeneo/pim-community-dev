<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Queue;

use Akeneo\Tool\Component\Messenger\Tenant\TenantAwareInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * Object representing the message pushed into a queue to process a scheduled job.
 *
 * @author    JMLeroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ScheduledJobMessageInterface extends TenantAwareInterface
{
    public static function createScheduledJobMessage(
        string $jobCode,
        array $options
    ): ScheduledJobMessageInterface;

    public static function createFromNormalized(array $normalized): ScheduledJobMessageInterface;

    public function getJobCode(): string;

    public function getOptions(): array;
}
