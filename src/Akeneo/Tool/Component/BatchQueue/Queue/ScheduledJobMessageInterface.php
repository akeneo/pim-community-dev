<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Queue;

use Akeneo\Tool\Component\Messenger\Tenant\TenantAwareInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * @author    JM leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface ScheduledJobMessageInterface extends TenantAwareInterface
{
    public static function createNormalized(array $normalized): self;

    public function getId(): UuidInterface;

    public function getJobExecutionId(): ?int;

    public function getCreateTime(): \DateTime;

    public function getUpdatedTime(): ?\DateTime;

    public function getOptions(): array;
}
