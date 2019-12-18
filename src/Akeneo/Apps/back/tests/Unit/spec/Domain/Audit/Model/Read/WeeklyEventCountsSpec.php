<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Domain\Audit\Model\Read;

use Akeneo\Apps\Domain\Audit\Model\Read\DailyEventCount;
use Akeneo\Apps\Domain\Audit\Model\Read\WeeklyEventCounts;
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
        $this->beConstructedWith('magento');
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(WeeklyEventCounts::class);
    }

    public function it_normalizes_an_app(): void
    {
        $this->normalize()->shouldReturn(
            [
                'magento' => [],
            ]
        );
    }

    public function it_normalizes_an_app_with_event_counts(): void
    {
        $eventDate1 = $eventDate = new \DateTime('2019-12-12', new \DateTimeZone('UTC'));
        $eventCount1 = new DailyEventCount(153, $eventDate1);

        $eventDate2 = $eventDate = new \DateTime('2019-12-13', new \DateTimeZone('UTC'));
        $eventCount2 = new DailyEventCount(231, $eventDate2);

        $eventDate3 = $eventDate = new \DateTime('2019-12-14', new \DateTimeZone('UTC'));
        $eventCount3 = new DailyEventCount(127, $eventDate3);

        $this->beConstructedWith('magento');
        $this->addDailyEventCount($eventCount1);
        $this->addDailyEventCount($eventCount2);
        $this->addDailyEventCount($eventCount3);

        $this->normalize()->shouldReturn(
            [
                'magento' => [
                    '2019-12-12' => 153,
                    '2019-12-13' => 231,
                    '2019-12-14' => 127,
                ],
            ]
        );
    }
}
