<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Bundle\EventSubscriber;

use Akeneo\Pim\Automation\RuleEngine\Bundle\EventSubscriber\RefreshIndexesBeforeRuleSelectionSubscriber;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\Rule;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\GenericEvent;

class RefreshIndexesBeforeRuleSelectionSubscriberSpec extends ObjectBehavior
{
    function let(Client $productAndProductModelClient)
    {
        $this->beConstructedWith($productAndProductModelClient);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RefreshIndexesBeforeRuleSelectionSubscriber::class);
    }

    function it_is_a_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_refreshes_indexes(
        Client $productAndProductModelClient,
        RuleEvent $event
    ) {
        $this->refreshIndexes($event);

        $productAndProductModelClient->refreshIndex()->shouldBeCalled();
    }
}
