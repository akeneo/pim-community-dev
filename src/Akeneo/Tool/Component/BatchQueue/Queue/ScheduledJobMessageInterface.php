<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Queue;

use Akeneo\Tool\Component\Messenger\Tenant\TenantAwareInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface ScheduledJobMessageInterface extends TenantAwareInterface
{
    public static function createScheduledJobMessage(
        string $jobCode,
        array $options
    ): ScheduledJobMessageInterface;

    public static function createFromNormalized(array $normalized): ScheduledJobMessageInterface;

    public function getId(): UuidInterface;

    public function getJobCode(): string;

    public function getCreateTime(): \DateTime;

    public function getUpdatedTime(): ?\DateTime;

    public function getOptions(): array;
}
