<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Audit\Command;

use Akeneo\Connectivity\Connection\Application\Audit\Command\UpdateDataDestinationProductEventCountHandler;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Repository\EventCountRepository;
use PhpSpec\ObjectBehavior;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateDataDestinationProductEventCountHandlerSpec extends ObjectBehavior
{
    function let(EventCountRepository $eventCountRepository)
    {
        $this->beConstructedWith($eventCountRepository);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(UpdateDataDestinationProductEventCountHandler::class);
    }
}
