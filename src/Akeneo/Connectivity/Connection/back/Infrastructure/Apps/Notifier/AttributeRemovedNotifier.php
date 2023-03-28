<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Notifier;

use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Connectivity\Connection\Application\Apps\Notifier\AttributeRemovedNotifierInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindAllUsernamesWithAclQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindOneConnectedAppByUserIdentifierQueryInterface;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeRemovedNotifier implements AttributeRemovedNotifierInterface
{
    public function __construct(
        private FindAllUsernamesWithAclQueryInterface $findAllUsernamesWithAclQuery,
        private FindOneConnectedAppByUserIdentifierQueryInterface $findOneConnectedAppByUserIdentifierQuery,
        private NotifierInterface $notifier,
    ) {
    }

    public function notify(Catalog $catalog): void
    {
        $connectedApp = $this->findOneConnectedAppByUserIdentifierQuery->execute($catalog->getOwnerUsername());
        if (null === $connectedApp) {
            return; // do not notify if the catalog does not belong to a connected app
        }

        $usersToNotify = $this->findAllUsernamesWithAclQuery->execute('akeneo_connectivity_connection_manage_apps');

        $this->notifier->notify($this->createNotification($catalog, $connectedApp), $usersToNotify);
    }

    private function createNotification(Catalog $catalog, ConnectedApp $connectedApp): NotificationInterface
    {
        $notification = new Notification();
        $notification
            ->setType('error')
            ->setMessage('pim_notification.attribute_removed.message')
            ->setMessageParams(['{{ catalog_name }}' => $catalog->getName()])
            ->setRoute('akeneo_connectivity_connection_connect_connected_apps_catalogs_edit')
            ->setRouteParams([
                'connectionCode' => $connectedApp->getConnectionCode(),
                'catalogId' => $catalog->getId(),
            ])
            ->setContext([
                'buttonLabel' => 'pim_notification.attribute_removed.button_label',
                'actionType' => 'attribute_removed',
            ])
        ;

        return $notification;
    }
}
