<?php

namespace Specification\Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\PubSub;

use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\PubSub\PubSubStatusChecker;
use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\PubSub\PubSubStatusCheckerInterface;
use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\ServiceStatus;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\GooglePubSub\PubSubClientFactory;
use Google\Cloud\Core\Exception\ServiceException;
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use Google\Cloud\PubSub\Topic;
use PhpSpec\ObjectBehavior;

class PubSubStatusCheckerSpec extends ObjectBehavior
{
    function let(
        PubSubClientFactory $pubSubClientFactory,
        PubSubClient $pubSubClient,
        Topic $topic,
        Subscription $subscription
    ) {
        $pubSubClientFactory->createPubSubClient(['projectId' => 'a_project_id'])
            ->willReturn($pubSubClient);

        $pubSubClient->topic('a_topic_id')
            ->willReturn($topic);

        $topic->subscription('a_subscription_id')
            ->willReturn($subscription);

        $this->beConstructedWith($pubSubClientFactory, 'a_project_id', 'a_topic_id', 'a_subscription_id');
    }

    function it_is_a_pubsub_status_checker()
    {
        $this->shouldBeAnInstanceOf(PubSubStatusChecker::class);
        $this->shouldImplement(PubSubStatusCheckerInterface::class);
    }

    function it_returns_ok_status_if_pubsub_is_available($subscription)
    {
        $subscription->pull([
            'maxMessages' => 1,
            'returnImmediately' => true,
        ])->willReturn([]);

        $this->status()->shouldBeLike(ServiceStatus::ok());
    }

    function it_returns_not_ok_status_if_pubsub_is_not_available($subscription)
    {
        $subscription->pull([
            'maxMessages' => 1,
            'returnImmediately' => true,
        ])->willThrow(new ServiceException('Some Google error.'));

        $this->status()->shouldBeLike(ServiceStatus::notOk('Unable to access Pub/Sub: Some Google error.'));
    }

    function it_rejects_any_message_pulled_immediatly($subscription, Message $message)
    {
        $subscription->pull([
            'maxMessages' => 1,
            'returnImmediately' => true,
        ])->willReturn([$message]);

        $subscription->modifyAckDeadline($message, 0)->shouldBeCalled();

        $this->status()->shouldBeLike(ServiceStatus::ok());
    }
}
