<?php

namespace spec\Pim\Bundle\UserBundle\EventSubscriber\Storage;

use Akeneo\Component\StorageUtils\StorageEvents;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclSidManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\Role;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

class RemoveAclRoleSidSubscriberSpec extends ObjectBehavior
{
    function let(AclSidManager $aclSidManager)
    {
        $this->beConstructedWith($aclSidManager);
    }

    function it_subscribes_to_some_events()
    {
        $this->getSubscribedEvents()->shouldReturn([StorageEvents::POST_REMOVE => 'removeAclSid']);
    }

    function it_does_nothing_on_wrong_subject($aclSidManager, GenericEvent $event)
    {
        $event->getSubject()->willReturn('subject');
        $aclSidManager->deleteSid(Argument::any())->shouldNotBeCalled();

        $this->removeAclSid($event);
    }

    function it_does_nothing_if_acl_not_enabled($aclSidManager, GenericEvent $event, Role $role)
    {
        $aclSidManager->isAclEnabled()->willReturn(false);
        $event->getSubject()->willReturn($role);
        $aclSidManager->deleteSid(Argument::any())->shouldNotBeCalled();

        $this->removeAclSid($event);
    }

    function it_deletes_acl_otherwise($aclSidManager, GenericEvent $event, Role $role, SecurityIdentityInterface $sid)
    {
        $aclSidManager->isAclEnabled()->willReturn(true);
        $event->getSubject()->willReturn($role);
        $aclSidManager->getSid($role)->willReturn($sid);
        $aclSidManager->deleteSid($sid)->shouldBeCalled();

        $this->removeAclSid($event);
    }
}
