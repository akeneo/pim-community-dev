<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Audit\Query;

use Akeneo\Connectivity\Connection\Application\Audit\Query\GetErrorCountPerConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Audit\Query\GetErrorCountPerConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\ErrorCount;
use Akeneo\Connectivity\Connection\Domain\Audit\Model\Read\ErrorCountPerConnection;
use Akeneo\Connectivity\Connection\Domain\Audit\Persistence\SelectErrorCountPerConnectionQueryInterface;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use PhpSpec\ObjectBehavior;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetErrorCountPerConnectionHandlerSpec extends ObjectBehavior
{
    public function let(SelectErrorCountPerConnectionQueryInterface $selectErrorCountPerConnectionQuery): void
    {
        $this->beConstructedWith($selectErrorCountPerConnectionQuery);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(GetErrorCountPerConnectionHandler::class);
    }

    public function it_handles_the_get_error_count($selectErrorCountPerConnectionQuery): void
    {
        $fromDateTime = new \DateTimeImmutable('2020-05-10 00:00:00', new \DateTimeZone('UTC'));
        $upToDateTime = new \DateTimeImmutable('2020-05-12 00:00:00', new \DateTimeZone('UTC'));

        $errorCountPerConnection = new ErrorCountPerConnection([
            new ErrorCount('erp', 11),
            new ErrorCount('bynder', 21),
        ]);
        $selectErrorCountPerConnectionQuery->execute(ErrorTypes::BUSINESS, $fromDateTime, $upToDateTime)
            ->willReturn($errorCountPerConnection);

        $query = new GetErrorCountPerConnectionQuery(ErrorTypes::BUSINESS, $fromDateTime, $upToDateTime);
        $this->handle($query)->shouldReturn($errorCountPerConnection);
    }
}
