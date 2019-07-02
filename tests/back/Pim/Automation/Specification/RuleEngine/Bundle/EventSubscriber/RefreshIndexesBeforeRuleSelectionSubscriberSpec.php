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
    function let(Client $productClient, Client $productAndProductModelClient, Client $productModelClient)
    {
        $this->beConstructedWith($productClient, $productAndProductModelClient, $productModelClient);
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
        Client $productClient,
        Client $productAndProductModelClient,
        Client $productModelClient,
        RuleEvent $event
    ) {
        $this->refreshIndexes($event);

        $productClient->refreshIndex()->shouldBeCalled();
        $productAndProductModelClient->refreshIndex()->shouldBeCalled();
        $productModelClient->refreshIndex()->shouldBeCalled();
    }
}
