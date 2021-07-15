<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

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
    private FeatureFlag $featureFlag;

    public function __construct(FeatureFlag $featureFlag)
    {
        $this->featureFlag = $featureFlag;
    }

    public static function getSubscribedEvents()
    {
        return [PrivilegesPostLoadEvent::class => 'handle'];
    }

    public function handle(PrivilegesPostLoadEvent $event)
    {
        if (true === $this->featureFlag->isEnabled()) {
            return;
        }

        $event->setPrivileges(
            $event->getPrivileges()->filter(fn (AclPrivilege $privilege) =>
                'action:akeneo_connectivity_connection_manage_apps' !== $privilege->getIdentity()->getId()
            )
        );
    }
}
