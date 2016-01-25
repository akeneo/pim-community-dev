<?php

namespace spec\Pim\Bundle\UserBundle\EventSubscriber\Doctrine;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclSidManager;
use Oro\Bundle\SecurityBundle\DependencyInjection\Utils\ServiceLink;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\Role;
use Prophecy\Argument;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

class RoleSubscriberSpec extends ObjectBehavior
{
    function let(ServiceLink $link, AclSidManager $sidManager)
    {
        $link->getService()->willReturn($sidManager);

        $this->beConstructedWith($link);
    }

    function it_is_a_doctrine_event_subscriber()
    {
        $this->shouldHaveType('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_some_events()
    {
        $this->getSubscribedEvents()->shouldReturn(['preUpdate']);
    }

    function it_doesnt_update_sid_on_non_role_objects($sidManager, PreUpdateEventArgs $event)
    {
        $sidManager->updateSid(Argument::any(), Argument::any())->shouldNotBeCalled();
        $event->getEntity()->willReturn(new \stdClass());

        $this->preUpdate($event);
    }

    function it_doesnt_update_sid_if_role_did_not_change($sidManager, PreUpdateEventArgs $event, Role $role)
    {
        $sidManager->updateSid(Argument::any(), Argument::any())->shouldNotBeCalled();

        $event->getEntity()->willReturn($role);
        $event->hasChangedField('role')->willReturn(false);

        $this->preUpdate($event);
    }

    function it_updates_sid($sidManager, PreUpdateEventArgs $event, Role $role, SecurityIdentityInterface $sid)
    {
        $sidManager->updateSid($sid, 'old')->shouldBeCalled();

        $event->getEntity()->willReturn($role);
        $event->hasChangedField('role')->willReturn(true);
        $event->getOldValue('role')->willReturn('old');
        $event->getNewValue('role')->willReturn('new');

        $sidManager->getSid('new')->willReturn($sid);

        $this->preUpdate($event);
    }
}
