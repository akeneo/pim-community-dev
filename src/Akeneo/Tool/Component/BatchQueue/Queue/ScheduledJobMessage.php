<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\BatchQueue\Queue;

/**
 * @author    JMLeroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScheduledJobMessage implements ScheduledJobMessageInterface
{
    private ?string $tenantId = null;

    private function __construct(
        private string $jobCode,
        private array $options
    ) {
    }

    public static function createScheduledJobMessage(
        string $jobCode,
        array $options
    ): ScheduledJobMessageInterface {
        return new static($jobCode, $options);
    }

    public static function createFromNormalized(array $normalized): ScheduledJobMessageInterface
    {
        return new static(
            $normalized['job_code'],
            $normalized['options'] ?? []
        );
    }

    public function getJobCode(): string
    {
        return $this->jobCode;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function setTenantId(string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }
}
