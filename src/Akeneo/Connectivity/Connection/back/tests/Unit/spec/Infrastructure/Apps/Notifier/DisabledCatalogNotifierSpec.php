<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\Notifier;

use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Connectivity\Connection\Application\Apps\Notifier\DisabledCatalogNotifierInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindAllUsernamesWithAclQueryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Notifier\DisabledCatalogNotifier;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DisabledCatalogNotifierSpec extends ObjectBehavior
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

    public function it_is_a_disabled_catalog_notifier(): void
    {
        $this->shouldHaveType(DisabledCatalogNotifier::class);
        $this->shouldImplement(DisabledCatalogNotifierInterface::class);
    }

    public function it_notifies_all_users_that_can_manage_apps(
        FindAllUsernamesWithAclQueryInterface $findAllUsernamesWithAclQuery,
        NotifierInterface $notifier,
    ): void {
        $usersThatShouldBeNotified = ['userA', 'userB', 'userC'];

        $findAllUsernamesWithAclQuery
            ->execute('akeneo_connectivity_connection_manage_apps')
            ->willReturn($usersThatShouldBeNotified);

        $this->notify(new Catalog('catalog_id', 'Catalog name', 'owner_username', false));

        $notifier
            ->notify(Argument::type(NotificationInterface::class), $usersThatShouldBeNotified)
            ->shouldHaveBeenCalled();
    }
}
