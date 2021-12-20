<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Settings\Query;

use Akeneo\Connectivity\Connection\Application\Settings\Query\IsConnectionNumberLimitReachedQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\CountAllConnectionsQueryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsConnectionNumberLimitReachedQuerySpec extends ObjectBehavior
{
    public function let(CountAllConnectionsQueryInterface $countAllConnectionsQuery)
    {
        $this->beConstructedWith($countAllConnectionsQuery, 50);
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(IsConnectionNumberLimitReachedQuery::class);
    }

    public function it_returns_true_when_connection_count_is_above_the_limit(
        CountAllConnectionsQueryInterface $countAllConnectionsQuery
    ) {
        $countAllConnectionsQuery->execute()->willReturn(150);
        $this->execute()->shouldReturn(true);

        $countAllConnectionsQuery->execute()->willReturn(51);
        $this->execute()->shouldReturn(true);

        $countAllConnectionsQuery->execute()->willReturn(50);
        $this->execute()->shouldReturn(true);
    }

    public function it_returns_false_when_connection_count_is_under_the_limit(
        CountAllConnectionsQueryInterface $countAllConnectionsQuery
    ) {
        $countAllConnectionsQuery->execute()->willReturn(0);
        $this->execute()->shouldReturn(false);

        $countAllConnectionsQuery->execute()->willReturn(25);
        $this->execute()->shouldReturn(false);

        $countAllConnectionsQuery->execute()->willReturn(49);
        $this->execute()->shouldReturn(false);
    }

}
