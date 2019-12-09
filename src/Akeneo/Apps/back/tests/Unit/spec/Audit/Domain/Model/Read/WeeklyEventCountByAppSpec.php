<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Audit\Domain\Model\Read;

use Akeneo\Apps\Audit\Domain\Model\Read\EventCountByDate;
use Akeneo\Apps\Audit\Domain\Model\Read\WeeklyEventCountByApp;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class WeeklyEventCountByAppSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('magento', 'product_created', []);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(WeeklyEventCountByApp::class);
    }

    function it_normalizes_an_app()
    {
        $this->normalize()->shouldReturn([
            'app_label' => 'magento',
            'event_type' => 'product_created',
            'event_counts' => [],
        ]);
    }

    function it_normalizes_an_app_with_event_counts()
    {
        $eventDate1 = $eventDate = new \DateTime('2019-12-12', new \DateTimeZone('UTC'));
        $eventCount1 = new EventCountByDate(153, $eventDate1);

        $eventDate2 = $eventDate = new \DateTime('2019-12-13', new \DateTimeZone('UTC'));
        $eventCount2 = new EventCountByDate(231, $eventDate2);

        $eventDate3 = $eventDate = new \DateTime('2019-12-14', new \DateTimeZone('UTC'));
        $eventCount3 = new EventCountByDate(127, $eventDate3);

        $this->beConstructedWith('magento', 'product_updated', [$eventCount1, $eventCount2, $eventCount3]);
        $this->normalize()->shouldReturn(
            [
                'app_label' => 'magento',
                'event_type' => 'product_updated',
                'event_counts' => [
                    '2019-12-12' => 153,
                    '2019-12-13' => 231,
                    '2019-12-14' => 127,
                ]
            ]
        );
    }
}
