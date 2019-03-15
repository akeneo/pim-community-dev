<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\Authentication\Sso\Configuration;

/**
 * Expiration date of a certificate.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
final class CertificateExpirationDate
{
    /** @var \DateTimeImmutable */
    private $expirationDate;

    public function __construct(string $date)
    {
        // TODO: throw domain exceptions ?
        if ('' === $date) {
            throw new \InvalidArgumentException('Certificate expiration date cannot be empty.');
        }

        try {
            $expirationDate = new \DateTimeImmutable($date);
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException(sprintf(
                'Certificate expiration date "%s" cannot be parsed.',
                $date
            ));
        }

        $this->expirationDate = $expirationDate;
    }

    public function toDateTime(): \DateTime
    {
        // For the moment there is no other way to build a DateTime from a DateTimeImmutable.
        // Use DateTime::createFromImmutable() when we'll support PHP 7.3.
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($this->expirationDate->getTimestamp());
        $dateTime->setTimezone($this->expirationDate->getTimezone());

        return $dateTime;
    }

    public function doesExpireInLessThanDays(\DateTimeImmutable $now, int $numberOfDays): bool
    {
        return $this->expirationDate->diff($now)->days < $numberOfDays;
    }
}
