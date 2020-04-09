<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure;

use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Repository\ConnectionRepository;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\AreCredentialsValidCombinationQuery;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Query\SelectConnectionCodeByClientIdQuery;
use Akeneo\Connectivity\Connection\Infrastructure\ConnectionContext;
use PhpSpec\ObjectBehavior;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ConnectionContextSpec extends ObjectBehavior
{
    function let(
        AreCredentialsValidCombinationQuery $areCredentialsValidCombinationQuery,
        SelectConnectionCodeByClientIdQuery $selectConnectionCode,
        ConnectionRepository $connectionRepository
    ) {
        $this->beConstructedWith($areCredentialsValidCombinationQuery, $selectConnectionCode, $connectionRepository);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(ConnectionContext::class);
    }
}
