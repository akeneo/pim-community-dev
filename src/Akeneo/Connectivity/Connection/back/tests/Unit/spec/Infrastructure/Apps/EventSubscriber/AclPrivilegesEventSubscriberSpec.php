<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Apps\EventSubscriber;

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
        FeatureFlag $marketplaceFlag,
        FeatureFlag $developerModeFlag,
    ): void {
        $this->beConstructedWith($marketplaceFlag, $developerModeFlag);
    }

    public function it_provides_subscribed_events(): void
    {
        $this->getSubscribedEvents()->shouldReturn([PrivilegesPostLoadEvent::class => 'disableAclIfFeatureIsDisabled']);
    }

    public function it_does_nothing_if_the_feature_flags_are_enabled(
        FeatureFlag $marketplaceFlag,
        PrivilegesPostLoadEvent $event,
        FeatureFlag $developerModeFlag,
    ) {
        $marketplaceFlag->isEnabled()->willReturn(true);
        $developerModeFlag->isEnabled()->willReturn(true);
        $event->setPrivileges(Argument::any())->shouldNotBeCalled();

        $this->disableAclIfFeatureIsDisabled($event);
    }

    public function it_filters_acls_if_the_marketplace_flag_is_disabled(
        FeatureFlag $marketplaceFlag,
        FeatureFlag $developerModeFlag,
        PrivilegesPostLoadEvent $event,
        ArrayCollection $privileges,
        ArrayCollection $filteredPrivileges
    ) {
        $marketplaceFlag->isEnabled()->willReturn(false);
        $developerModeFlag->isEnabled()->willReturn(true);
        $event->getPrivileges()->willReturn($privileges);
        $privileges->filter(Argument::any())->willReturn($filteredPrivileges);

        $event->setPrivileges($filteredPrivileges)->shouldBeCalledOnce();

        $this->disableAclIfFeatureIsDisabled($event);
    }

    public function it_filters_acls_if_the_developer_mode_flag_is_disabled(
        FeatureFlag $marketplaceFlag,
        FeatureFlag $developerModeFlag,
        PrivilegesPostLoadEvent $event,
        ArrayCollection $privileges,
        ArrayCollection $filteredPrivileges
    ) {
        $marketplaceFlag->isEnabled()->willReturn(true);
        $developerModeFlag->isEnabled()->willReturn(false);
        $event->getPrivileges()->willReturn($privileges);
        $privileges->filter(Argument::any())->willReturn($filteredPrivileges);

        $event->setPrivileges($filteredPrivileges)->shouldBeCalledOnce();

        $this->disableAclIfFeatureIsDisabled($event);
    }

    public function it_filters_acls_if_both_flags_are_disabled(
        FeatureFlag $marketplaceFlag,
        FeatureFlag $developerModeFlag,
        PrivilegesPostLoadEvent $event,
        ArrayCollection $marketplacePrivileges,
        ArrayCollection $developerModePrivileges,
        ArrayCollection $marketplaceFilteredPrivileges,
        ArrayCollection $devFilteredPrivileges,
    ) {
        $marketplaceFlag->isEnabled()->willReturn(false);
        $developerModeFlag->isEnabled()->willReturn(false);
        $event->getPrivileges()->willReturn($marketplacePrivileges, $developerModePrivileges);
        $marketplacePrivileges->filter(Argument::any())->willReturn($marketplaceFilteredPrivileges);
        $developerModePrivileges->filter(Argument::any())->willReturn($devFilteredPrivileges);

        $event->setPrivileges($marketplaceFilteredPrivileges)->shouldBeCalledOnce();
        $event->setPrivileges($devFilteredPrivileges)->shouldBeCalledOnce();

        $this->disableAclIfFeatureIsDisabled($event);
    }
}
