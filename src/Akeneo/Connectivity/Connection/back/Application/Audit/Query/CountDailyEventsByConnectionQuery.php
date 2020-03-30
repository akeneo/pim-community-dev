<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Audit\Query;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CountDailyEventsByConnectionQuery
{
    /** @var string */
    private $eventType;

    /** @var \DateTimeInterface */
    private $fromDateTime;

    /** @var \DateTimeInterface */
    private $upToDateTime;

    public function __construct(
        string $eventType,
        \DateTimeImmutable $fromDateTime,
        \DateTimeImmutable $upToDateTime
    ) {
        $this->eventType = $eventType;

        $this->checkTimezoneIsUtc($fromDateTime);
        $this->fromDateTime = $fromDateTime;

        $this->checkTimezoneIsUtc($upToDateTime);
        $this->upToDateTime = $upToDateTime;
    }

    public function eventType(): string
    {
        return $this->eventType;
    }

    public function fromDateTime(): \DateTimeImmutable
    {
        return $this->fromDateTime;
    }

    public function upToDateTime(): \DateTimeImmutable
    {
        return $this->upToDateTime;
    }

    private function checkTimezoneIsUtc(\DateTimeImmutable $dateTime): void
    {
        if ('UTC' !== $dateTime->getTimezone()->getName() && '+00:00' !== $dateTime->getTimezone()->getName()) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Parameter `$dateTime` "%s" with timezone "%s" must have a timezone "UTC".',
                    $dateTime->format(\DateTimeInterface::ATOM),
                    $dateTime->getTimezone()->getName()
                )
            );
        }
    }
}
