<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\FlagAppContainingOutdatedScopesCommand;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlagAppContainingOutdatedScopesCommandSpec extends ObjectBehavior
{
    private ConnectedApp $connectedApp;

    public function let(): void
    {
        $this->connectedApp = new ConnectedApp(
            'a_connected_app_id',
            'a_connected_app_name',
            ['a_scope'],
            'random_connection_code',
            'a/path/to/a/logo',
            'an_author',
            'a_group',
            'an_username',
            [],
            false,
            null,
            true,
        );

        $this->beConstructedWith($this->connectedApp, 'requested scopes');
    }

    public function it_is_instantiable(): void
    {
        $this->shouldHaveType(FlagAppContainingOutdatedScopesCommand::class);
    }

    public function it_returns_connected_app(): void
    {
        $this->getConnectedApp()->shouldReturn($this->connectedApp);
    }

    public function it_returns_requested_scopes(): void
    {
        $this->getRequestedScopes()->shouldReturn('requested scopes');
    }
}
