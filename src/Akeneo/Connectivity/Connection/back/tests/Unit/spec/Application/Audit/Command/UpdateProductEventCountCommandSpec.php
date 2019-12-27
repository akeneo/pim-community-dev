<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Audit\Command;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateProductEventCountCommand;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateProductEventCountCommandSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('2019-O9-13');
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(UpdateProductEventCountCommand::class);
    }

    function it_returns_the_event_date()
    {
        $this->beConstructedWith('2019-09-13');
        $this->eventDate()->shouldReturn('2019-09-13');
    }

    function it_throws_an_exception_is_the_event_date_is_not_a_correct_date_format()
    {
        $this->beConstructedWith('2019-091');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
