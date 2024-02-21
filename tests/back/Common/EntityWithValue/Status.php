<?php

declare(strict_types=1);

namespace Akeneo\Test\Common\EntityWithValue;

final class Status
{
    /** @var bool */
    private $status;

    /**
     * @param bool $status
     */
    private function __construct(bool $status)
    {
        $this->status = $status;
    }

    /**
     * @return Status
     */
    public static function enabled(): Status
    {
        return new self(true);
    }

    /**
     * @return Status
     */
    public static function disabled(): Status
    {
        return new self(false);
    }

    /**
     * @param bool $boolean
     *
     * @return Status
     */
    public static function fromBoolean(bool $boolean): Status
    {
        return new self($boolean);
    }

    /**
     * @return bool
     */
    public function toStandardFormat(): bool
    {
        return $this->status;
    }
}
