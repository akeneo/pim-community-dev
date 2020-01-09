<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Settings\Model\Read;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Client;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ClientSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            42,
            'my_client_id',
            'my_secret'
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Client::class);
    }

    public function it_returns_the_id()
    {
        $this->id()->shouldReturn(42);
    }

    public function it_returns_the_client_id()
    {
        $this->clientId()->shouldReturn('my_client_id');
    }

    public function it_returns_the_secret()
    {
        $this->secret()->shouldReturn('my_secret');
    }
}
