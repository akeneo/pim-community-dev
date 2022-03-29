<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\FlagAppContainingOutdatedScopesCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\FlagAppContainingOutdatedScopesHandler;
use Akeneo\Connectivity\Connection\Application\Apps\Security\ScopeMapperRegistryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\SaveConnectedAppOutdatedScopesFlagQueryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlagAppContainingOutdatedScopesHandlerSpec extends ObjectBehavior
{
    public function let(
        ScopeMapperRegistryInterface $scopeMapperRegistry,
        SaveConnectedAppOutdatedScopesFlagQueryInterface $saveConnectedAppOutdatedScopesFlagQuery
    ): void {
        $this->beConstructedWith($scopeMapperRegistry, $saveConnectedAppOutdatedScopesFlagQuery);
    }

    public function it_is_a_flag_app_containing_outdated_scopes_handler(): void
    {
        $this->shouldHaveType(FlagAppContainingOutdatedScopesHandler::class);
    }

    public function it_updates_has_outdated_scopes_flag(
        ScopeMapperRegistryInterface $scopeMapperRegistry,
        SaveConnectedAppOutdatedScopesFlagQueryInterface $saveConnectedAppOutdatedScopesFlagQuery,
    ): void {
        $scopeMapperRegistry->getAllScopes()->willReturn([
            'allowed_scope_a',
            'allowed_scope_b',
            'allowed_scope_c',
            'allowed_scope_d',
        ]);

        $saveConnectedAppOutdatedScopesFlagQuery
            ->execute('a_connected_app_id', true)
            ->shouldBeCalled();

        $this->handle(new FlagAppContainingOutdatedScopesCommand(
            new ConnectedApp(
                'a_connected_app_id',
                'a_connected_app_name',
                ['allowed_scope_d allowed_scope_b' ],
                'random_connection_code',
                'a/path/to/a/logo',
                'an_author',
                'a_group',
            ),
            'allowed_scope_a openid_scope_a random noise allowed_scope_b'
        ));
    }

    public function it_does_not_update_has_outdated_scopes_flag(
        ScopeMapperRegistryInterface $scopeMapperRegistry,
        SaveConnectedAppOutdatedScopesFlagQueryInterface $saveConnectedAppOutdatedScopesFlagQuery,
    ): void {
        $scopeMapperRegistry->getAllScopes()->willReturn([
            'allowed_scope_a',
            'allowed_scope_b',
            'allowed_scope_c',
            'allowed_scope_d',
        ]);

        $saveConnectedAppOutdatedScopesFlagQuery
            ->execute('a_connected_app_id', true)
            ->shouldNotBeCalled();

        $this->handle(new FlagAppContainingOutdatedScopesCommand(
            new ConnectedApp(
                'a_connected_app_id',
                'a_connected_app_name',
                ['allowed_scope_d', 'allowed_scope_b', 'allowed_scope_c'],
                'random_connection_code',
                'a/path/to/a/logo',
                'an_author',
                'a_group',
            ),
            'allowed_scope_c openid_scope_a random noise allowed_scope_b allowed_scope_d'
        ));
    }
}
