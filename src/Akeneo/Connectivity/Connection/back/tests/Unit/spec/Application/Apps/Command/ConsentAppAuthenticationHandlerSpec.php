<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\ConsentAppAuthenticationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\CreateUserConsentQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\GetAppConfirmationQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Clock;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConsentAppAuthenticationHandlerSpec extends ObjectBehavior
{
    public function let(
        GetAppConfirmationQueryInterface $getAppConfirmationQuery,
        AppAuthorizationSessionInterface $appAuthorizationSession,
        CreateUserConsentQueryInterface $createUserConsentQuery,
        Clock $clock,
        ValidatorInterface $validator
    ): void {
        $this->beConstructedWith(
            $getAppConfirmationQuery,
            $appAuthorizationSession,
            $createUserConsentQuery,
            $clock,
            $validator
        );
    }

    public function it_is_instantiable()
    {
        $this->shouldHaveType(ConsentAppAuthenticationHandler::class);
    }

    public function it_creates_the_user_consent(): void
    {
    }

    public function it_throws_when_the_scope_openid_is_not_requested(): void
    {
    }

    public function it_throws_when_the_command_is_not_valid(ValidatorInterface $validator): void
    {
    }

    public function it_throws_when_the_app_authorization_is_not_found(): void
    {
    }

    public function it_throws_when_the_app_confirmation_is_not_found(): void
    {
    }
}
