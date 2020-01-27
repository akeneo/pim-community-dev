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
        $this->beConstructedWith('magento', '2020-01-20', '2020-01-28', []);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(WeeklyEventCounts::class);
    }

    public function it_normalizes_a_connection_without_data(): void
    {
        $this->beConstructedWith('magento', '2020-01-01', '2020-01-08', []);

        $this->normalize()->shouldReturn(
            [
                'magento' => [
                    '2020-01-01' => 0,
                    '2020-01-02' => 0,
                    '2020-01-03' => 0,
                    '2020-01-04' => 0,
                    '2020-01-05' => 0,
                    '2020-01-06' => 0,
                    '2020-01-07' => 0,
                    '2020-01-08' => 0,
                ],
            ]
        );
    }

    public function it_normalizes_a_connection_with_partial_data(): void
    {
        $this->beConstructedWith('magento', '2020-01-01', '2020-01-08', [
            '2020-01-02' => 2,
            '2020-01-03' => 10,
            '2020-01-10' => 5,
        ]);

        $this->normalize()->shouldReturn(
            [
                'magento' => [
                    '2020-01-01' => 0,
                    '2020-01-02' => 2,
                    '2020-01-03' => 10,
                    '2020-01-04' => 0,
                    '2020-01-05' => 0,
                    '2020-01-06' => 0,
                    '2020-01-07' => 0,
                    '2020-01-08' => 0,
                ],
            ]
        );
    }
}
