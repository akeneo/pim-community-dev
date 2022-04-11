<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\EventSubscriber;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\Acl\Event\PrivilegesPostLoadEvent;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AclPrivilegesEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private FeatureFlag $marketplaceActivateFlag,
        private FeatureFlag $developerModeFlag,
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [PrivilegesPostLoadEvent::class => 'disableAclIfFeatureIsDisabled'];
    }

    public function disableAclIfFeatureIsDisabled(PrivilegesPostLoadEvent $event)
    {
        if (false === $this->marketplaceActivateFlag->isEnabled()) {
            $event->setPrivileges(
                $event->getPrivileges()->filter(
                    fn (AclPrivilege $privilege) =>
                        'action:akeneo_connectivity_connection_manage_apps' !== $privilege->getIdentity()->getId()
                        && 'action:akeneo_connectivity_connection_open_apps' !== $privilege->getIdentity()->getId()
                )
            );
        }

        if (false === $this->developerModeFlag->isEnabled()) {
            $event->setPrivileges(
                $event->getPrivileges()->filter(
                    fn (AclPrivilege $privilege) =>
                        'action:akeneo_connectivity_connection_manage_test_apps' !== $privilege->getIdentity()->getId()
                )
            );
        }
    }
}
