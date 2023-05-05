<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationCommand;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConsentAppAuthenticationCommandSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('a_client_id', 1);
    }

    public function it_is_instantiable(): void
    {
        $this->shouldHaveType(ConsentAppAuthenticationCommand::class);
    }

    public function it_gets_client_id(): void
    {
        $this->getClientId()->shouldReturn('a_client_id');
    }

    public function it_gets_pim_user_id(): void
    {
        $this->getPimUserId()->shouldReturn(1);
    }
}
