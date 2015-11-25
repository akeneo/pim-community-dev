<?php

namespace Pim\Bundle\UserBundle\EventSubscriber\Storage;

use Akeneo\Component\StorageUtils\StorageEvents;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclSidManager;
use Pim\Bundle\UserBundle\Entity\Role;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class RemoveAclRoleSidSubscriber
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveAclRoleSidSubscriber implements EventSubscriberInterface
{
    /** @var AclSidManager $aclSidManager */
    protected $aclSidManager;

    /**
     * @param AclSidManager $aclSidManager
     */
    public function __construct(AclSidManager $aclSidManager)
    {
        $this->aclSidManager = $aclSidManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_REMOVE => 'removeAclSid',
        ];
    }

    /**
     * Pre delete a user group
     *
     * @param GenericEvent $event
     */
    public function removeAclSid(GenericEvent $event)
    {
        $role = $event->getSubject();

        if (!$role instanceof Role) {
            return;
        }

        if (!$this->aclSidManager->isAclEnabled()) {
            return;
        }

        $this->aclSidManager->deleteSid($this->aclSidManager->getSid($role));
    }
}
