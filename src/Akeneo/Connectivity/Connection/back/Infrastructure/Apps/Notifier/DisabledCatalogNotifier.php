<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Notifier;

use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Connectivity\Connection\Application\Apps\Notifier\DisabledCatalogNotifierInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindAllUsernamesWithAclQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByUserIdentifierQueryInterface;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DisabledCatalogNotifier implements DisabledCatalogNotifierInterface
{
    public function __construct(
        private FindAllUsernamesWithAclQueryInterface $findAllUsernamesWithAclQuery,
        private FindOneConnectedAppByUserIdentifierQueryInterface $findOneConnectedAppByUserIdentifierQuery,
        private NotifierInterface $notifier,
    ) {
    }

    public function notify(Catalog $catalog): void
    {
        $usersToNotify = $this->findAllUsernamesWithAclQuery->execute('akeneo_connectivity_connection_manage_apps');

        $this->notifier->notify($this->createNotification($catalog), $usersToNotify);
    }

    private function createNotification(Catalog $catalog): NotificationInterface
    {
        $connectedApp = $this->findOneConnectedAppByUserIdentifierQuery->execute($catalog->getOwnerUsername());
        if (null === $connectedApp) {
            throw new \LogicException('Connected App not found.');
        }

        $notification = new Notification();
        $notification
            ->setType('error')
            ->setMessage('pim_notification.disabled_catalog.message')
            ->setMessageParams(['{{ catalog_name }}' => $catalog->getName()])
            ->setRoute('akeneo_connectivity_connection_connect_connected_apps_catalogs_edit')
            ->setRouteParams([
                'connectionCode' => $connectedApp->getConnectionCode(),
                'catalogId' => $catalog->getId(),
            ])
            ->setContext([
                'buttonLabel' => 'pim_notification.disabled_catalog.button_label',
                'actionType' => 'disabled_catalog',
            ])
        ;

        return $notification;
    }
}
