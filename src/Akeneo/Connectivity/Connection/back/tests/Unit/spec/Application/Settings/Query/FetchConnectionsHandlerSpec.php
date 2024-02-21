<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Settings\Query;

use Akeneo\Connectivity\Connection\Application\Settings\Query\FetchConnectionsHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FetchConnectionsQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Connection;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\SelectConnectionsQueryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FetchConnectionsHandlerSpec extends ObjectBehavior
{
    public function let(SelectConnectionsQueryInterface $selectConnectionsQuery): void
    {
        $this->beConstructedWith($selectConnectionsQuery);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(FetchConnectionsHandler::class);
    }

    public function it_fetches_connections(SelectConnectionsQueryInterface $selectConnectionsQuery): void
    {
        $connections = [
            new Connection('42', 'magento', 'Magento Connector', FlowType::DATA_DESTINATION, true),
            new Connection('43', 'bynder', 'Bynder DAM', FlowType::OTHER, false),
        ];

        $selectConnectionsQuery->execute([ConnectionType::DEFAULT_TYPE])->willReturn($connections);

        $query = new FetchConnectionsQuery(['types' => [ConnectionType::DEFAULT_TYPE]]);

        $this->handle($query)->shouldReturn($connections);
    }
}
