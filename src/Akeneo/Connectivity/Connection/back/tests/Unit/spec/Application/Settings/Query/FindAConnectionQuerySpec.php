<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Settings\Query;

use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionQuery;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FindAConnectionQuerySpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('bynder');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(FindAConnectionQuery::class);
    }

    public function it_returns_a_connection_code()
    {
        $this->connectionCode()->shouldReturn('bynder');
    }
}
