<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Application\Audit\Command;

use Akeneo\Apps\Application\Audit\Command\UpdateProductEventCountHandler;
use Akeneo\Apps\Domain\Audit\Persistence\Query\ExtractConnectionsProductEventCountQuery;
use Akeneo\Apps\Domain\Audit\Persistence\Repository\EventCountRepository;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UpdateProductEventCountHandlerSpec extends ObjectBehavior
{
    function let(ExtractConnectionsProductEventCountQuery $extractConnectionsEventCountQuery, EventCountRepository $eventCountRepository)
    {
        $this->beConstructedWith($extractConnectionsEventCountQuery, $eventCountRepository);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(UpdateProductEventCountHandler::class);
    }
}
