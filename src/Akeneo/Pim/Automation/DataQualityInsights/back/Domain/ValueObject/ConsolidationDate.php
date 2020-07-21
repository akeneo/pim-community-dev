<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

final class ConsolidationDate
{
    /** @var \DateTimeImmutable */
    private $dateTime;

    public function __construct(\DateTimeImmutable $dateTimeImmutable)
    {
        $this->dateTime = $dateTimeImmutable;
    }

    public function getDateTime(): \DateTimeImmutable
    {
        return $this->dateTime;
    }

    public function isLastDayOfWeek(): bool
    {
        return '7' === $this->dateTime->format('N');
    }

    public function isLastDayOfMonth(): bool
    {
        return $this->dateTime->format('d') === $this->dateTime->format('t');
    }

    public function isLastDayOfYear(): bool
    {
        return '12-31' === $this->dateTime->format('m-d');
    }

    public function format(string $format = 'Y-m-d'): string
    {
        return $this->dateTime->format($format);
    }

    public function modify(string $modify): self
    {
        return new self($this->dateTime->modify($modify));
    }
}
