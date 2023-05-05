<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Audit\Persistence\Traits;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\AllConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\HourlyEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\PeriodEventCount;
use Akeneo\Connectivity\Connection\Domain\ValueObject\DateTimePeriod;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
trait PeriodEventCountTrait
{
    /**
     * @param string[] $connectionCodes
     * @param array<array{connection_code: string, event_datetime: ?string, event_count: ?string}> $data
     *
     * @return PeriodEventCount[]
     */
    private function createPeriodEventCountPerConnection(
        DateTimePeriod $period,
        array $connectionCodes,
        array $data
    ): array {
        $hourlyEventCountsPerConnection = \array_reduce(
            $data,
            function (array $data, array $row): array {
                $connectionCode = $row['connection_code'];

                if (!isset($data[$connectionCode])) {
                    $data[$connectionCode] = [];
                }

                $data[$connectionCode][] = new HourlyEventCount(
                    \DateTimeImmutable::createFromFormat(
                        'Y-m-d H:i:s',
                        $row['event_datetime'],
                        new \DateTimeZone('UTC')
                    ),
                    (int) $row['event_count']
                );

                return $data;
            },
            []
        );

        $periodEventCountPerConnection = [];
        $periodEventCountPerConnection[] = new PeriodEventCount(
            AllConnectionCode::CODE,
            $period->start(),
            $period->end(),
            $hourlyEventCountsPerConnection[AllConnectionCode::CODE] ?? []
        );
        foreach ($connectionCodes as $connectionCode) {
            $periodEventCountPerConnection[] = new PeriodEventCount(
                $connectionCode,
                $period->start(),
                $period->end(),
                $hourlyEventCountsPerConnection[$connectionCode] ?? []
            );
        }

        return $periodEventCountPerConnection;
    }
}
