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
        return $this->dateTime->format('d') ===  $this->dateTime->format('t');
    }

    public function formatByPeriodicity(Periodicity $periodicity): string
    {
        switch (strval($periodicity)) {
            case Periodicity::DAILY:
                return $this->dateTime->format('Y-m-d');
            case Periodicity::WEEKLY:
                return $this->dateTime->format('Y-W');
            case Periodicity::MONTHLY:
                return $this->dateTime->format('Y-m');
            default:
                throw new \InvalidArgumentException(sprintf('The periodicity %s is not supported', $periodicity));
        }
    }

    public function modify(string $modify): self
    {
        return new self($this->dateTime->modify($modify));
    }
}
