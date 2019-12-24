<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Application\Settings\Query;

use Akeneo\Apps\Application\Settings\Query\FindAConnectionHandler;
use Akeneo\Apps\Application\Settings\Query\FindAConnectionQuery;
use Akeneo\Apps\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Apps\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Apps\Domain\Settings\Persistence\Query\SelectConnectionWithCredentialsByCodeQuery;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FindAConnectionHandlerSpec extends ObjectBehavior
{
    public function let(SelectConnectionWithCredentialsByCodeQuery $selectConnectionWithCredentialsByCodeQuery)
    {
        $this->beConstructedWith($selectConnectionWithCredentialsByCodeQuery);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(FindAConnectionHandler::class);
    }

    public function it_returns_a_connection($selectConnectionWithCredentialsByCodeQuery)
    {
        $connection = new ConnectionWithCredentials('bynder', 'Bynder DAM', FlowType::OTHER, 'client_id', 'secret', 'username');

        $selectConnectionWithCredentialsByCodeQuery->execute('bynder')->willReturn($connection);

        $query = new FindAConnectionQuery('bynder');
        $this->handle($query)->shouldReturn($connection);
    }

    public function it_returns_null_when_the_connection_does_not_exists($selectConnectionWithCredentialsByCodeQuery)
    {
        $selectConnectionWithCredentialsByCodeQuery->execute('bynder')->willReturn(null);

        $query = new FindAConnectionQuery('bynder');
        $this->handle($query)->shouldReturn(null);
    }
}
