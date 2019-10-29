<?php

declare(strict_types=1);

namespace spec\Akeneo\Apps\Application\Query;

use Akeneo\Apps\Application\Query\FetchAppQuery;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FetchAppQuerySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('bynder');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FetchAppQuery::class);
    }

    function it_returns_an_application_code()
    {
        $this->appCode()->shouldReturn('bynder');
    }
}
