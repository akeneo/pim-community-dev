<?php

declare(strict_types=1);

namespace Oro\Bundle\SecurityBundle\EventSubscriber;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\SecurityBundle\Acl\Event\PrivilegesPostLoadEvent;
use Oro\Bundle\SecurityBundle\Model\AclFeatureFlags;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FeatureFlagAclPrivilegesEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AclFeatureFlags $aclFeatureFlags
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [PrivilegesPostLoadEvent::class => 'disableAclIfFeatureIsDisabled'];
    }

    public function disableAclIfFeatureIsDisabled(PrivilegesPostLoadEvent $event)
    {
        $event->setPrivileges(
            $event->getPrivileges()->filter(
                function (AclPrivilege $privilege): bool {
                    [$action, $aclName] = explode(':', $privilege->getIdentity()->getId());
                    if (null === $aclName || !$this->aclFeatureFlags->hasAcl($aclName)) {
                        return false;
                    }

                    return $this->aclFeatureFlags->isAclAvailable($aclName);
                }
            )
        );
    }
}
