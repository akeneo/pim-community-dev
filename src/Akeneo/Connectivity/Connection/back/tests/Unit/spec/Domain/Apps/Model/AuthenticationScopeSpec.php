<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Apps\Model;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\AuthenticationScope;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthenticationScopeSpec extends ObjectBehavior
{
    public function it_is_an_authentication_scope(): void
    {
        $this->shouldHaveType(AuthenticationScope::class);
    }

    public function it_returns_all_the_scopes(): void
    {
        $this::getAllScopes()->shouldReturn(
            [AuthenticationScope::SCOPE_OPENID, AuthenticationScope::SCOPE_PROFILE, AuthenticationScope::SCOPE_EMAIL]
        );
    }
}
