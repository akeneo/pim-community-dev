<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit\Controller\Internal;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractAuditAction
{
    /**
     * @return \DateTimeImmutable[]
     */
    protected function createUserDateTimeInterval(
        string $endDateUser,
        \DateTimeZone $timezone,
        \DateInterval $dateInterval
    ): array {
        $endDateTimeUser = \DateTimeImmutable::createFromFormat(
            'Y-m-d',
            $endDateUser,
            $timezone
        );
        if (false === $endDateTimeUser) {
            throw new \InvalidArgumentException(\sprintf(
                'Unexpected format for the `end_date` parameter "%s". Format must be `Y-m-d`',
                $endDateUser
            ));
        }

        $startDateTimeUser = $endDateTimeUser->sub($dateInterval);

        return [$startDateTimeUser, $endDateTimeUser];
    }

    /**
     * @return \DateTimeImmutable[]
     */
    protected function createUtcDateTimeInterval(
        \DateTimeImmutable $startDateTimeUser,
        \DateTimeImmutable $endDateTimeUser
    ): array {
        $fromDateTime = $startDateTimeUser
            ->setTime(0, 0)
            ->setTimezone(new \DateTimeZone('UTC'));

        $upToDateTime = $endDateTimeUser
            ->setTime(0, 0)
            ->add(new \DateInterval('P1D'))
            ->setTimezone(new \DateTimeZone('UTC'));

        return [$fromDateTime, $upToDateTime];
    }
}
