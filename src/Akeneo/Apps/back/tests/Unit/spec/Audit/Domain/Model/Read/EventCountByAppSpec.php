<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Audit\Domain\Model\Read;

use Akeneo\Apps\Audit\Domain\Model\Read\EventCountByApp;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class EventCountByAppSpec extends ObjectBehavior
{
    function let()
    {
        $eventDate = new \DateTime('2019-12-03', new \DateTimeZone('UTC'));
        $this->beConstructedWith('magento', 'product_created', 5, $eventDate);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(EventCountByApp::class);
    }

    function it_normalizes_an_app()
    {
        $this->normalize()->shouldReturn([
            'app_code' => 'magento',
            'event_type' => 'product_created',
            'event_count' => 5,
            'event_date' => '2019-12-03',
        ]);
    }
}
