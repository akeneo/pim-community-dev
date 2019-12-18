<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Application\Audit\Command;

use Akeneo\Apps\Application\Audit\Command\PopulateProductEventsCountCommand;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PopulateProductEventsCountCommandSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('product_created', '2019-12-10');
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(PopulateProductEventsCountCommand::class);
    }

    function it_returns_the_event_type()
    {
        $this->eventType()->shouldReturn('product_created');
    }

    function it_returns_the_event_date()
    {
        $this->eventDate()->shouldReturn('2019-12-10');
    }
}
