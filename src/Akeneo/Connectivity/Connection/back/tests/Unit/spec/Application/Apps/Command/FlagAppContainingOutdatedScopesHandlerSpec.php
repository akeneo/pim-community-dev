<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\FlagAppContainingOutdatedScopesCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\FlagAppContainingOutdatedScopesHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Notifier\AuthorizationRequestNotifierInterface;
use Akeneo\Connectivity\Connection\Application\Apps\Security\ScopeMapperRegistryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\SaveConnectedAppOutdatedScopesFlagQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\ScopeListComparator;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlagAppContainingOutdatedScopesHandlerSpec extends ObjectBehavior
{
    public function let(
        ScopeMapperRegistryInterface $scopeMapperRegistry,
        SaveConnectedAppOutdatedScopesFlagQueryInterface $saveConnectedAppOutdatedScopesFlagQuery,
        AuthorizationRequestNotifierInterface $authorizationRequestNotifier,
    ): void {
        $scopeMapperRegistry->getAllScopes()->willReturn([
            'read_scope_a',
            'write_scope_a',
            'read_scope_b',
            'write_scope_b',
            'read_scope_c',
            'read_scope_d',
        ]);

        $scopeMapperRegistry
            ->getExhaustiveScopes(['read_scope_a', 'write_scope_b'])
            ->willReturn([
                'read_scope_a',
                'read_scope_b',
                'write_scope_b',
            ]);

        $scopeMapperRegistry
            ->getExhaustiveScopes(['read_scope_d'])
            ->willReturn(['read_scope_d']);

        $scopeMapperRegistry
            ->getExhaustiveScopes(['read_scope_b', 'read_scope_d'])
            ->willReturn([
                'read_scope_b',
                'read_scope_d',
            ]);

        $this->beConstructedWith(
            $scopeMapperRegistry,
            $saveConnectedAppOutdatedScopesFlagQuery,
            $authorizationRequestNotifier,
            new ScopeListComparator($scopeMapperRegistry->getWrappedObject()),
        );
    }

    public function it_is_a_flag_app_containing_outdated_scopes_handler(): void
    {
        $this->shouldHaveType(FlagAppContainingOutdatedScopesHandler::class);
    }

    public function it_flags_the_connected_app_on_new_scopes(
        SaveConnectedAppOutdatedScopesFlagQueryInterface $saveConnectedAppOutdatedScopesFlagQuery,
        AuthorizationRequestNotifierInterface $authorizationRequestNotifier,
    ): void {
        $connectedApp = new ConnectedApp(
            'a_connected_app_id',
            'a_connected_app_name',
            ['read_scope_d', 'read_scope_b'],
            'random_connection_code',
            'a/path/to/a/logo',
            'an_author',
            'a_group',
            'an_username',
        );

        $this->handle(new FlagAppContainingOutdatedScopesCommand(
            $connectedApp,
            'read_scope_a openid_scope_a random noise write_scope_b'
        ));

        $saveConnectedAppOutdatedScopesFlagQuery
            ->execute('a_connected_app_id', true)
            ->shouldHaveBeenCalled();

        $authorizationRequestNotifier
            ->notify($connectedApp)
            ->shouldHaveBeenCalled();
    }

    public function it_does_not_flag_the_connected_app_on_less_scopes(
        SaveConnectedAppOutdatedScopesFlagQueryInterface $saveConnectedAppOutdatedScopesFlagQuery,
        AuthorizationRequestNotifierInterface $authorizationRequestNotifier,
    ): void {
        $connectedApp = new ConnectedApp(
            'a_connected_app_id',
            'a_connected_app_name',
            ['read_scope_d', 'read_scope_b'],
            'random_connection_code',
            'a/path/to/a/logo',
            'an_author',
            'a_group',
            'an_username',
        );

        $this->handle(new FlagAppContainingOutdatedScopesCommand(
            $connectedApp,
            'openid_scope_a random noise read_scope_d'
        ));

        $saveConnectedAppOutdatedScopesFlagQuery
            ->execute('a_connected_app_id', true)
            ->shouldNotHaveBeenCalled();

        $authorizationRequestNotifier
            ->notify($connectedApp)
            ->shouldNotHaveBeenCalled();
    }

    public function it_does_not_flag_the_connected_app_on_same_scopes(
        SaveConnectedAppOutdatedScopesFlagQueryInterface $saveConnectedAppOutdatedScopesFlagQuery,
        AuthorizationRequestNotifierInterface $authorizationRequestNotifier,
    ): void {
        $connectedApp = new ConnectedApp(
            'a_connected_app_id',
            'a_connected_app_name',
            ['read_scope_d', 'read_scope_b'],
            'random_connection_code',
            'a/path/to/a/logo',
            'an_author',
            'a_group',
            'an_username',
        );

        $this->handle(new FlagAppContainingOutdatedScopesCommand(
            $connectedApp,
            'read_scope_b openid_scope_a random noise read_scope_d'
        ));

        $saveConnectedAppOutdatedScopesFlagQuery
            ->execute('a_connected_app_id', true)
            ->shouldNotHaveBeenCalled();

        $authorizationRequestNotifier
            ->notify($connectedApp)
            ->shouldNotHaveBeenCalled();
    }
}
