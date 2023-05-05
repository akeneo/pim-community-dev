<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\ValueObject;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class HourlyInterval
{
    private function __construct(private \DateTimeInterface $fromDateTime, private \DateTimeInterface $upToDateTime)
    {
    }

    public function fromDateTime(): \DateTimeInterface
    {
        return $this->fromDateTime;
    }

    public function upToDateTime(): \DateTimeInterface
    {
        return $this->upToDateTime;
    }

    /**
     * Create an hourly interval from a DateTime.
     * E.g., with a (Date)Time between 10:00:00 and 10:59:59 the interval will be 10:00:00 to 11:00:00.
     *
     * @param \DateTimeInterface $dateTime DateTimeInterface must have a TimeZone in 'UTC'.
     */
    public static function createFromDateTime(\DateTimeInterface $dateTime): self
    {
        if ('UTC' !== $dateTime->getTimezone()->getName() && '+00:00' !== $dateTime->getTimezone()->getName()) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Parameter `$dateTime` "%s" with timezone "%s" must have a timezone "UTC".',
                    $dateTime->format(\DateTimeInterface::ATOM),
                    $dateTime->getTimezone()->getName()
                )
            );
        }

        $fromDateTime = \DateTimeImmutable::createFromFormat(
            \DateTimeInterface::ATOM,
            $dateTime->format(\DateTimeInterface::ATOM)
        );
        if (false === $fromDateTime) {
            throw new \RuntimeException();
        }
        $fromDateTime = $fromDateTime->setTime((int) $dateTime->format('H'), 0, 0);

        $upToDateTime = $fromDateTime->add(new \DateInterval('PT1H'));

        return new self($fromDateTime, $upToDateTime);
    }

    /**
     * Check if the hourly interval is equals to the given one.
     */
    public function equals(self $hourlyInterval): bool
    {
        return $this->upToDateTime()->getTimestamp() === $hourlyInterval->upToDateTime()->getTimestamp();
    }
}
