<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AppAuthorization;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperRegistry;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestAppAuthorizationHandlerSpec extends ObjectBehavior
{
    public function let(
        ValidatorInterface $validator,
        AppAuthorizationSessionInterface $session,
        ScopeMapperInterface $scopeMapperChannel,
    ): void {
        $scopeMapperChannel->getScopes()->willReturn(['read_channel_localization', 'read_channel_settings']);
        $scopeMapperRegistry = new ScopeMapperRegistry([$scopeMapperChannel->getWrappedObject()]);
        $this->beConstructedWith(
            $validator,
            $session,
            $scopeMapperRegistry,
        );
    }

    public function it_is_a_request_app_authorization_handler(): void
    {
        $this->shouldHaveType(RequestAppAuthorizationHandler::class);
    }

    public function it_should_initialize_the_session(
        ValidatorInterface $validator,
        AppAuthorizationSessionInterface $session,
    ): void {
        $command = new RequestAppAuthorizationCommand(
            'client_id',
            'response_type',
            'read_channel_localization',
            'http://url.test',
        );
        $validator->validate($command)->willReturn([]);

        $session->initialize(Argument::type(AppAuthorization::class))
            ->shouldBeCalledOnce();

        $this->handle($command);
    }
}
