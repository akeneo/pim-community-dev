<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CriterionEvaluationStatus
{
    public const PENDING = 'pending';
    public const IN_PROGRESS = 'in_progress';
    public const DONE = 'done';
    public const TIMEOUT = 'timeout';
    public const ERROR = 'error';

    private const STATUS_LIST = [
        self::PENDING,
        self::IN_PROGRESS,
        self::DONE,
        self::TIMEOUT,
        self::ERROR,
    ];

    /** @var string */
    private $status;

    public function __construct(string $status)
    {
        if ('' === $status) {
            throw new \InvalidArgumentException('The status can not be an empty string.');
        }

        if (!in_array($status, self::STATUS_LIST)) {
            throw new \InvalidArgumentException(sprintf('The status "%s" does not exist.', $status));
        }

        $this->status = $status;
    }

    public function __toString()
    {
        return $this->status;
    }

    public function isPending(): bool
    {
        return self::PENDING === $this->status;
    }

    public function isInProgress(): bool
    {
        return self::IN_PROGRESS === $this->status;
    }

    public function isDone(): bool
    {
        return self::DONE === $this->status;
    }

    public function isError(): bool
    {
        return self::ERROR === $this->status;
    }

    public function isTimeOut(): bool
    {
        return self::TIMEOUT === $this->status;
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function inProgress(): self
    {
        return new self(self::IN_PROGRESS);
    }

    public static function done(): self
    {
        return new self(self::DONE);
    }

    public static function timeout(): self
    {
        return new self(self::TIMEOUT);
    }

    public static function error(): self
    {
        return new self(self::ERROR);
    }
}
