<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Audit\Model;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class HourlyInterval
{
    /** @var \DateTimeInterface */
    private $fromDateTime;

    /** @var \DateTimeInterface */
    private $upToDateTime;

    private function __construct(\DateTimeInterface $fromDateTime, \DateTimeInterface $upToDateTime)
    {
        $this->fromDateTime = $fromDateTime;
        $this->upToDateTime = $upToDateTime;
    }

    public function fromDateTime(): \DateTimeInterface
    {
        return $this->fromDateTime;
    }

    public function upToDateTime(): \DateTimeInterface
    {
        return $this->upToDateTime;
    }

    public static function createFromDateTime(\DateTimeInterface $dateTime): self
    {
        if ('UTC' !== $dateTime->getTimezone()->getName()) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Parameter `$dateTime` "%s" with timezone "%s" should have a timezone "UTC".',
                    $dateTime->format(\DateTimeInterface::ATOM),
                    $dateTime->getTimezone()->getName()
                )
            );
        }

        $fromDateTime = \DateTimeImmutable::createFromFormat(
            \DateTimeInterface::ATOM,
            $dateTime->format(\DateTimeInterface::ATOM)
        )->setTime((int) $dateTime->format('H'), 0, 0);

        $upToDateTime = $fromDateTime->add(new \DateInterval('PT1H'));

        return new self($fromDateTime, $upToDateTime);
    }

    public static function equals(self $firstInterval, self $secondInterval): bool
    {
        return $firstInterval->upToDateTime() == $secondInterval->upToDateTime();
    }
}
