<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\ErrorManagement\Query;

use Akeneo\Connectivity\Connection\Application\ErrorManagement\Query\GetConnectionBusinessErrorsQuery;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetConnectionBusinessErrorsQuerySpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('erp', '2020-01-01');
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(GetConnectionBusinessErrorsQuery::class);
    }

    public function it_returns_the_connection_code(): void
    {
        $this->connectionCode()->shouldReturn('erp');
    }

    public function it_returns_the_end_date(): void
    {
        $this->endDate()->shouldReturn('2020-01-01');
    }

    public function it_returns_null_if_there_is_no_end_date(): void
    {
        $this->beConstructedWith('erp', null);

        $this->endDate()->shouldReturn(null);
    }
}
