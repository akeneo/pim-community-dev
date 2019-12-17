<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Domain\Audit\Model\Read;

use Akeneo\Apps\Domain\Audit\Model\Read\WeeklyEventCounts;
use Akeneo\Apps\Domain\Audit\Model\Read\DailyEventCount;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AppEventCountsSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('magento');
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(WeeklyEventCounts::class);
    }

    function it_normalizes_an_app()
    {
        $this->normalize()->shouldReturn([
            'magento' => [],
        ]);
    }

    function it_normalizes_an_app_with_event_counts()
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
                    ['date' => '2019-12-12', 'value' => 153],
                    ['date' => '2019-12-13', 'value' => 231],
                    ['date' => '2019-12-14', 'value' => 127],
                ]
            ]
        );
    }
}
