<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Factory;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RuleExecutionNotificationFactorySpec extends ObjectBehavior
{
    const NOTIFICATION_CLASS = 'Pim\Bundle\NotificationBundle\Entity\Notification';

    function let()
    {
        $this->beConstructedWith(static::NOTIFICATION_CLASS);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Factory\RuleExecutionNotificationFactory');
    }

    function it_is_a_notification_factory()
    {
        $this->shouldImplement('Pim\Bundle\NotificationBundle\Factory\NotificationFactoryInterface');
    }

    function it_returns_a_factory()
    {
        $this->create(Argument::any())->shouldReturnAnInstanceOf(static::NOTIFICATION_CLASS);
    }
}
