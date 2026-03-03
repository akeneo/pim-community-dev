<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthenticationCommand;
use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RequestAppAuthenticationCommandSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith(
            'an_app_id',
            1,
            ScopeList::fromScopes(['an_authentication_scope', 'another_authentication_scope'])
        );
    }

    public function it_is_a_request_app_authentication_command(): void
    {
        $this->shouldHaveType(RequestAppAuthenticationCommand::class);
    }

    public function it_returns_app_id(): void
    {
        $this->getAppId()->shouldReturn('an_app_id');
    }

    public function it_returns_pim_user_id(): void
    {
        $this->getPimUserId()->shouldReturn(1);
    }

    public function it_returns_requested_authentication_scopes(): void
    {
        $this->getRequestedAuthenticationScopes()->toScopeString()->shouldReturn(
            ScopeList::fromScopes(['an_authentication_scope', 'another_authentication_scope'])->toScopeString()
        );
    }
}
