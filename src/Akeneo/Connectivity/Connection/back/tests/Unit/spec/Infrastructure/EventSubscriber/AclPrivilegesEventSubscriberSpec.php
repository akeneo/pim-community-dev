<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\EventSubscriber;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Acl\Event\PrivilegesPostLoadEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AclPrivilegesEventSubscriberSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $featureFlag
    ): void {
        $this->beConstructedWith($featureFlag);
    }

    public function it_provides_subscribed_events(): void
    {
        $this->getSubscribedEvents()->shouldReturn([PrivilegesPostLoadEvent::class => 'disableAclIfFeatureIsDisabled']);
    }

    public function it_does_nothing_if_the_feature_flag_is_enabled(
        FeatureFlag $featureFlag,
        PrivilegesPostLoadEvent $event
    ) {
        $featureFlag->isEnabled()->willReturn(true);
        $event->setPrivileges(Argument::any())->shouldNotBeCalled();

        $this->disableAclIfFeatureIsDisabled($event);
    }

    public function it_filter_acls_if_the_feature_flag_is_disabled(
        FeatureFlag $featureFlag,
        PrivilegesPostLoadEvent $event,
        ArrayCollection $privileges,
        ArrayCollection $filteredPrivileges
    ) {
        $featureFlag->isEnabled()->willReturn(false);
        $event->getPrivileges()->willReturn($privileges);
        $privileges->filter(Argument::any())->willReturn($filteredPrivileges);

        $event->setPrivileges($filteredPrivileges)->shouldBeCalled();

        $this->disableAclIfFeatureIsDisabled($event);
    }
}
