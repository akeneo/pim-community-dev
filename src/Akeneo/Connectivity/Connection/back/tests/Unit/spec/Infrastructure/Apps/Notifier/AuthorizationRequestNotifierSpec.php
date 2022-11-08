<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Notifier;

use Akeneo\Connectivity\Connection\Application\Apps\Notifier\AuthorizationRequestNotifierInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindAllUsernamesWithAclQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Notifier\AuthorizationRequestNotifier;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthorizationRequestNotifierSpec extends ObjectBehavior
{
    public function let(
        FindAllUsernamesWithAclQueryInterface $findAllUsernamesWithAclQuery,
        NotifierInterface $notifier,
    ): void {
        $this->beConstructedWith(
            $findAllUsernamesWithAclQuery,
            $notifier,
        );
    }

    public function it_is_authorization_request_notifier(): void
    {
        $this->shouldHaveType(AuthorizationRequestNotifier::class);
        $this->shouldImplement(AuthorizationRequestNotifierInterface::class);
    }

    public function it_notifies_all_users_that_can_manage_apps(
        FindAllUsernamesWithAclQueryInterface $findAllUsernamesWithAclQuery,
        NotifierInterface $notifier,
    ): void {
        $usersThatShouldBeNotified = ['userA', 'userB', 'userC'];

        $findAllUsernamesWithAclQuery
            ->execute('akeneo_connectivity_connection_manage_apps')
            ->willReturn($usersThatShouldBeNotified);

        $this->notify(new ConnectedApp(
            '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'App A',
            ['scope A1'],
            'connectionCodeA',
            'http://www.example.com/path/to/logo/a',
            'author A',
            'app_123456abcdef',
            'username_1234',
            ['category A1', 'category A2'],
            false,
            'partner A',
            true
        ));

        $notifier
            ->notify(Argument::type(NotificationInterface::class), $usersThatShouldBeNotified)
            ->shouldHaveBeenCalled();
    }
}
