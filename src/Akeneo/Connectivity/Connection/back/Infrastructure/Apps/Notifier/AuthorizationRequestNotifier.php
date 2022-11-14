<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Notifier;

use Akeneo\Connectivity\Connection\Application\Apps\Notifier\AuthorizationRequestNotifierInterface;
use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\FindAllUsernamesWithAclQueryInterface;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AuthorizationRequestNotifier implements AuthorizationRequestNotifierInterface
{
    public function __construct(
        private FindAllUsernamesWithAclQueryInterface $findAllUsernamesWithAclQuery,
        private NotifierInterface $notifier,
    ) {
    }

    public function notify(ConnectedApp $connectedApp): void
    {
        $usersToNotify = $this->findAllUsernamesWithAclQuery->execute('akeneo_connectivity_connection_manage_apps');

        $this->notifier->notify($this->createNotification($connectedApp), $usersToNotify);
    }

    private function createNotification(ConnectedApp $connectedApp): NotificationInterface
    {
        $notification = new Notification();
        $notification
            ->setType('warning')
            ->setMessage('pim_notification.connected_app_authorizations.message')
            ->setMessageParams(['{{ app_name }}' => $connectedApp->getName()])
            ->setRoute('akeneo_connectivity_connection_connect_connected_apps_open')
            ->setRouteParams(['connectionCode' => $connectedApp->getConnectionCode()])
            ->setContext([
                'buttonLabel' => 'pim_notification.connected_app_authorizations.button_label',
                'actionType' => 'connected_app_authorizations',
            ]);

        return $notification;
    }
}
