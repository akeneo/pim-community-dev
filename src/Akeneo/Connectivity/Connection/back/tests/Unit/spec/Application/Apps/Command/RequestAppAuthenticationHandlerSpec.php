<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RequestAppAuthenticationHandlerSpec extends ObjectBehavior
{
    public function it_clears_consented_scopes_when_the_openid_scope_is_not_requested(): void
    {
    }

    public function it_removes_non_requested_scopes_from_already_consented_scopes(): void
    {
    }

    public function it_throws_when_requested_scopes_are_not_yet_consented(): void
    {
    }

    public function it_doesnt_throw_when_requested_scopes_match_consented_scopes(): void
    {
    }
}
