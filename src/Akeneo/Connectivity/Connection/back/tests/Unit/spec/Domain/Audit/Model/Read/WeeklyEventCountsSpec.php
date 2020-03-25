<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Audit\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\WeeklyEventCounts;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class WeeklyEventCountsSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('magento', '2020-01-20', '2020-01-28', 'Europe/Paris', []);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(WeeklyEventCounts::class);
    }

    public function it_normalizes_a_connection_without_data(): void
    {
        $this->beConstructedWith('magento', '2020-01-01', '2020-01-08', 'Europe/Paris', []);

        $this->normalize()->shouldReturn(
            [
                'magento' => [
                    'Jan, 1' => 0,
                    'Jan, 2' => 0,
                    'Jan, 3' => 0,
                    'Jan, 4' => 0,
                    'Jan, 5' => 0,
                    'Jan, 6' => 0,
                    'Jan, 7' => 0,
                    'Jan, 8' => 0,
                ],
            ]
        );
    }

    public function it_normalizes_a_connection_with_partial_data(): void
    {
        $this->beConstructedWith('magento', '2020-01-01', '2020-01-08', 'Europe/Paris', [
            [new \DateTimeImmutable('2020-01-02 12:00:00', new \DateTimeZone('UTC')), 2],
            [new \DateTimeImmutable('2020-01-03 12:00:00', new \DateTimeZone('UTC')), 10],
            [new \DateTimeImmutable('2020-01-10 12:00:00', new \DateTimeZone('UTC')), 5],
        ]);

        $this->normalize()->shouldReturn(
            [
                'magento' => [
                    'Jan, 1' => 0,
                    'Jan, 2' => 2,
                    'Jan, 3' => 10,
                    'Jan, 4' => 0,
                    'Jan, 5' => 0,
                    'Jan, 6' => 0,
                    'Jan, 7' => 0,
                    'Jan, 8' => 0,
                ],
            ]
        );
    }

    public function it_normalizes_a_connection_with_data_and_handle_correctly_the_timezone(): void
    {
        $this->beConstructedWith(
            'magento',
            '2020-01-01',
            '2020-01-08',
            'Asia/Tokyo', // UTC+9 (no Daylight Saving Time)
            [
                [new \DateTimeImmutable('2020-01-02 14:00:00', new \DateTimeZone('UTC')), 3],
                [new \DateTimeImmutable('2020-01-02 15:00:00', new \DateTimeZone('UTC')), 5],
                [new \DateTimeImmutable('2020-01-03 14:00:00', new \DateTimeZone('UTC')), 10],
                [new \DateTimeImmutable('2020-01-03 15:00:00', new \DateTimeZone('UTC')), 6],
            ]
        );

        $this->normalize()->shouldReturn(
            [
                'magento' => [
                    'Jan, 1' => 0,
                    'Jan, 2' => 3,
                    'Jan, 3' => 15,
                    'Jan, 4' => 6,
                    'Jan, 5' => 0,
                    'Jan, 6' => 0,
                    'Jan, 7' => 0,
                    'Jan, 8' => 0,
                ],
            ]
        );
    }
}
