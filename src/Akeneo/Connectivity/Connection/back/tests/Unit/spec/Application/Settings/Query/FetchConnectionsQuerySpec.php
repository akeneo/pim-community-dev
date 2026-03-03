<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Settings\Query;

use Akeneo\Connectivity\Connection\Application\Settings\Query\FetchConnectionsQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionType;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FetchConnectionsQuerySpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->beConstructedWith([]);

        $this->shouldHaveType(FetchConnectionsQuery::class);
    }

    public function it_returns_types(): void
    {
        $this->beConstructedWith([
            'types' => [
                ConnectionType::DEFAULT_TYPE,
                ConnectionType::APP_TYPE,
            ],
        ]);

        $this->getTypes()->shouldReturn([ConnectionType::DEFAULT_TYPE, ConnectionType::APP_TYPE]);
    }

    public function it_returns_an_empty_type_list(): void
    {
        $this->beConstructedWith([]);
        $this->getTypes()->shouldReturn([]);
    }
}
