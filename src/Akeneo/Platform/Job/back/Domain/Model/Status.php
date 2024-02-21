<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Domain\Model;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Status
{
    public const COMPLETED = 1;
    public const STARTING = 2;
    public const IN_PROGRESS = 3;
    public const STOPPING = 4;
    public const STOPPED = 5;
    public const FAILED = 6;
    public const ABANDONED = 7;
    public const UNKNOWN = 8;
    public const PAUSING = 9;
    public const PAUSED = 10;

    public static array $labels = [
        self::COMPLETED => 'COMPLETED',
        self::STARTING => 'STARTING',
        self::IN_PROGRESS => 'IN_PROGRESS',
        self::STOPPING => 'STOPPING',
        self::STOPPED => 'STOPPED',
        self::FAILED => 'FAILED',
        self::ABANDONED => 'ABANDONED',
        self::UNKNOWN => 'UNKNOWN',
        self::PAUSING => 'PAUSING',
        self::PAUSED => 'PAUSED',
    ];

    public static function fromStatus(int $status): self
    {
        if (!array_key_exists($status, self::$labels)) {
            throw new \InvalidArgumentException(sprintf('Invalid status "%s"', $status));
        }

        return new self($status);
    }

    public static function fromLabel(string $status): self
    {
        if (!in_array($status, self::$labels)) {
            throw new \InvalidArgumentException(sprintf('Invalid label "%s"', $status));
        }

        return new self(array_flip(self::$labels)[$status]);
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getLabel(): string
    {
        return self::$labels[$this->status];
    }

    private function __construct(
        private int $status = self::UNKNOWN,
    ) {
    }
}
