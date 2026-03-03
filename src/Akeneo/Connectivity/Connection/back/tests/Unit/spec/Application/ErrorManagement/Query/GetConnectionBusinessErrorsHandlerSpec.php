<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\ErrorManagement\Query;

use Akeneo\Connectivity\Connection\Application\ErrorManagement\Query\GetConnectionBusinessErrorsHandler;
use Akeneo\Connectivity\Connection\Application\ErrorManagement\Query\GetConnectionBusinessErrorsQuery;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Query\SelectLastConnectionBusinessErrorsQueryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetConnectionBusinessErrorsHandlerSpec extends ObjectBehavior
{
    public function let(SelectLastConnectionBusinessErrorsQueryInterface $selectLastConnectionBusinessErrorsQuery): void
    {
        $this->beConstructedWith($selectLastConnectionBusinessErrorsQuery);
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(GetConnectionBusinessErrorsHandler::class);
    }

    public function it_returns_the_connection_business_errors($selectLastConnectionBusinessErrorsQuery): void
    {
        $selectLastConnectionBusinessErrorsQuery->execute('erp', '2020-01-01')->willReturn(['business_errors']);

        $query = new GetConnectionBusinessErrorsQuery('erp', '2020-01-01');
        $this->handle($query)->shouldReturn(['business_errors']);
    }
}
