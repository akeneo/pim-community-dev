<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\AppAuthorizationSessionInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RequestAppAuthorizationHandler;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\AccessDeniedException;
use Akeneo\Connectivity\Connection\Domain\Marketplace\GetAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperRegistry;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestAppAuthorizationHandlerSpec extends ObjectBehavior
{
    public function let(
        ValidatorInterface $validator,
        AppAuthorizationSessionInterface $session,
        GetAppQueryInterface $getAppQuery,
        SecurityFacade $security,
        ScopeMapperInterface $scopeMapperChannel,
    ): void {
        $scopeMapperChannel->getScopes()->willReturn(['read_channel_localization', 'read_channel_settings']);
        $scopeMapperRegistry = new ScopeMapperRegistry([$scopeMapperChannel->getWrappedObject()]);
        $this->beConstructedWith(
            $validator,
            $session,
            $scopeMapperRegistry,
            $getAppQuery,
            $security
        );
    }

    public function it_is_a_request_app_authorization_handler(): void
    {
        $this->shouldHaveType(RequestAppAuthorizationHandler::class);
    }

    public function it_throws_access_denied_exception_if_manage_test_apps_and_open_apps_are_not_granted(
        ValidatorInterface $validator,
        GetAppQueryInterface $getAppQuery,
        SecurityFacade $security,
    ): void {
        $command = new RequestAppAuthorizationCommand(
            'client_id',
            'response_type',
            'read_channel_localization',
        );
        $validator->validate($command)->willReturn([]);
        $app = App::fromTestAppValues([
            'id' => '12345',
            'name' => 'test app',
            'activate_url' => 'http://url.test',
            'callback_url' => 'http://url.test',
        ]);
        $getAppQuery->execute('client_id')->willReturn($app);
        $security->isGranted('akeneo_connectivity_connection_manage_test_apps')->willReturn(false);
        $security->isGranted('akeneo_connectivity_connection_open_apps')->willReturn(false);

        $this
            ->shouldThrow(new AccessDeniedException())
            ->during('handle', [$command]);
    }

    public function it_throws_access_denied_exception_if_manage_apps_and_open_apps_are_not_granted(
        ValidatorInterface $validator,
        GetAppQueryInterface $getAppQuery,
        SecurityFacade $security,
    ): void {
        $command = new RequestAppAuthorizationCommand(
            'client_id',
            'response_type',
            'read_channel_localization',
        );
        $validator->validate($command)->willReturn([]);
        $app = App::fromWebMarketplaceValues([
            'id' => '12345',
            'name' => 'test app',
            'activate_url' => 'http://url.test',
            'callback_url' => 'http://url.test',
            'logo' => 'logo',
            'author' => 'admin',
            'url' => 'http://manage_app.test',
            'categories' => ['master'],
        ]);
        $getAppQuery->execute('client_id')->willReturn($app);
        $security->isGranted('akeneo_connectivity_connection_manage_apps')->willReturn(false);
        $security->isGranted('akeneo_connectivity_connection_open_apps')->willReturn(false);

        $this
            ->shouldThrow(new AccessDeniedException())
            ->during('handle', [$command]);
    }
}
