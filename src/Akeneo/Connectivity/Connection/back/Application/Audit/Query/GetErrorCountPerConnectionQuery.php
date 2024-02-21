<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Audit\Query;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetErrorCountPerConnectionQuery
{
    private \DateTimeImmutable $fromDateTime;

    private \DateTimeImmutable $upToDateTime;

    public function __construct(
        private string $errorType,
        \DateTimeImmutable $fromDateTime,
        \DateTimeImmutable $upToDateTime
    ) {
        $this->checkTimezoneIsUtc($fromDateTime);
        $this->fromDateTime = $fromDateTime;

        $this->checkTimezoneIsUtc($upToDateTime);
        $this->upToDateTime = $upToDateTime;
    }

    public function errorType(): string
    {
        return $this->errorType;
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
        if ('UTC' !== $dateTime->getTimezone()->getName()) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Parameter `$dateTime` "%s" with timezone "%s" must have a timezone "UTC".',
                    $dateTime->format(\DateTimeInterface::ATOM),
                    $dateTime->getTimezone()->getName()
                )
            );
        }
    }
}
