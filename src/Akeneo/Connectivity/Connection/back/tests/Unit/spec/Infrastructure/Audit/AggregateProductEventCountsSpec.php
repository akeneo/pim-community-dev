<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Audit;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read;
use Akeneo\Connectivity\Connection\Infrastructure\Audit\AggregateProductEventCounts;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AggregateProductEventCountsSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(AggregateProductEventCounts::class);
    }

    public function it_normalizes_period_event_counts(): void
    {
        $periodEventCounts = [
            new Read\PeriodEventCount(
                '<all>',
                new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
                new \DateTimeImmutable('2020-01-03 00:00:00', new \DateTimeZone('UTC')),
                [
                    new Read\HourlyEventCount(
                        new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
                        10
                    )
                ]
            ),
            new Read\PeriodEventCount(
                'magento',
                new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC')),
                new \DateTimeImmutable('2020-01-05 00:00:00', new \DateTimeZone('UTC')),
                [
                    new Read\HourlyEventCount(
                        new \DateTimeImmutable('2020-01-04 23:00:00', new \DateTimeZone('UTC')),
                        100
                    )
                ]
            )
        ];
        $dateTimeZone = new \DateTimeZone('UTC');

        $this::normalize($periodEventCounts, $dateTimeZone)->shouldReturn([
            '<all>' => [
                ['2020-01-01', 10],
                ['2020-01-02', 0],
            ],
            'magento' => [
                ['2020-01-02', 0],
                ['2020-01-03', 0],
                ['2020-01-04', 100],
            ]
        ]);
    }

    public function it_normalizes_period_event_counts_with_the_user_timezone(): void
    {
        $periodEventCounts = [
            new Read\PeriodEventCount(
                '<all>',
                new \DateTimeImmutable('2019-12-31 15:00:00', new \DateTimeZone('UTC')),
                new \DateTimeImmutable('2020-01-03 15:00:00', new \DateTimeZone('UTC')),
                [
                    new Read\HourlyEventCount(
                        new \DateTimeImmutable('2019-12-31 15:00:00', new \DateTimeZone('UTC')),
                        10
                    ),
                    new Read\HourlyEventCount(
                        new \DateTimeImmutable('2020-01-01 15:00:00', new \DateTimeZone('UTC')),
                        100
                    ),
                    new Read\HourlyEventCount(
                        new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC')),
                        1000
                    ),
                    new Read\HourlyEventCount(
                        new \DateTimeImmutable('2020-01-02 15:00:00', new \DateTimeZone('UTC')),
                        10000
                    )
                ]
            ),
        ];
        $dateTimeZone = new \DateTimeZone('Asia/Tokyo'); // UTC+09:00 no Daylight saving time

        $this::normalize($periodEventCounts, $dateTimeZone)->shouldReturn([
            '<all>' => [
                ['2020-01-01', 10],
                ['2020-01-02', 1100],
                ['2020-01-03', 10000],
            ],
        ]);
    }
}
