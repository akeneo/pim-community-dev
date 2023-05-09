<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\CreateConnectedAppWithAuthorizationCommand;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CreateConnectedAppWithAuthorizationCommandSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('test');
    }

    public function it_is_instantiable(): void
    {
        $this->shouldHaveType(CreateConnectedAppWithAuthorizationCommand::class);
    }

    public function it_gets_client_id(): void
    {
        $this->getClientId()->shouldReturn('test');
    }
}
