<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Install;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection as DbalConnection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DbalConnection $dbalConnection,
        private AddAclToRoles $addAclToRoles,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstallerEvents::POST_DB_CREATE => ['updateSchema', -10],
            InstallerEvents::POST_LOAD_FIXTURES => ['loadFixtures', -20],
        ];
    }

    public function updateSchema(): void
    {
        $this->dbalConnection->executeStatement(CreateAppTableQuery::QUERY);
        $this->dbalConnection->executeStatement(CreateUserConsentTable::QUERY);
        $this->dbalConnection->executeStatement(CreateRevokedAppTokenTableQuery::QUERY);
    }

    public function loadFixtures(GenericEvent $event): void
    {
        if (!\str_ends_with($event->getArgument('catalog'), 'icecat_demo_dev')) {
            return;
        }

        $this->addAclToRoles->add('akeneo_connectivity_connection_manage_apps', ['ROLE_ADMINISTRATOR']);
        $this->addAclToRoles->add('akeneo_connectivity_connection_open_apps', ['ROLE_ADMINISTRATOR']);
    }
}
