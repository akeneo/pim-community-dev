<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Application\Audit\Query\GetPeriodErrorCountPerConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Audit\Query\GetPeriodErrorCountPerConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\SelectPeriodErrorCountPerConnectionQueryInterface;
use Akeneo\Connectivity\Connection\Domain\ValueObject\DateTimePeriod;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetPeriodErrorCountPerConnectionHandlerSpec extends ObjectBehavior
{
    public function let(SelectPeriodErrorCountPerConnectionQueryInterface $selectPeriodErrorCountPerConnectionQuery): void
    {
        $this->beConstructedWith($selectPeriodErrorCountPerConnectionQuery);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(GetPeriodErrorCountPerConnectionHandler::class);
    }

    public function it_handles_the_query($selectPeriodErrorCountPerConnectionQuery): void
    {
        $period = new DateTimePeriod(
            new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC'))
        );

        $periodErrorCountPerConnection = [];

        $selectPeriodErrorCountPerConnectionQuery->execute($period)
            ->willReturn($periodErrorCountPerConnection);

        $query = new GetPeriodErrorCountPerConnectionQuery($period);
        $this->handle($query)->shouldReturn($periodErrorCountPerConnection);
    }
}
