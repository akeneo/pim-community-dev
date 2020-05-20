<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Application\Audit\Query\GetPeriodEventCountPerConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Audit\Query\GetPeriodEventCountPerConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\EventTypes;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\PeriodEventCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\Query\SelectPeriodEventCountsQuery;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetPeriodEventCountPerConnectionHandlerSpec extends ObjectBehavior
{
    public function let(SelectPeriodEventCountsQuery $selectPeriodEventCountsQuery): void
    {
        $this->beConstructedWith($selectPeriodEventCountsQuery);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(GetPeriodEventCountPerConnectionHandler::class);
    }

    public function it_handles_the_event_count($selectPeriodEventCountsQuery): void
    {
        $fromDateTime = new \DateTimeImmutable('2020-01-01 00:00:00', new \DateTimeZone('UTC'));
        $upToDateTime = new \DateTimeImmutable('2020-01-02 00:00:00', new \DateTimeZone('UTC'));

        $periodEventCounts = [
            new PeriodEventCount('erp', $fromDateTime, $upToDateTime, [])
        ];
        $selectPeriodEventCountsQuery->execute(EventTypes::PRODUCT_CREATED, $fromDateTime, $upToDateTime)
            ->willReturn($periodEventCounts);

        $query = new GetPeriodEventCountPerConnectionQuery(EventTypes::PRODUCT_CREATED, $fromDateTime, $upToDateTime);
        $this->handle($query)->shouldReturn($periodEventCounts);
    }
}
