<?php

namespace Specification\Akeneo\Category\Infrastructure\EventSubscriber;

use Akeneo\Category\Application\Enrichment\CleanCategoryDataLinkedToChannel;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Infrastructure\EventSubscriber\CleanCategoryDataAfterChannelDeletionSubscriber;
use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class CleanCategoryDataAfterChannelDeletionSubscriberSpec extends ObjectBehavior
{
    function let(
        CleanCategoryDataLinkedToChannel $cleanCategoryDataLinkedToChannel,
        FeatureFlag $enrichedCategoryFeature
    )
    {
        $this->beConstructedWith($cleanCategoryDataLinkedToChannel, $enrichedCategoryFeature);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
        $this->shouldHaveType(CleanCategoryDataAfterChannelDeletionSubscriber::class);
    }

    function it_triggers_the_cleaning_of_category_after_channel_removal(
        GenericEvent $event,
        Channel $channel,
        CleanCategoryDataLinkedToChannel $cleanCategoryDataLinkedToChannel,
        FeatureFlag $enrichedCategoryFeature
    )
    {
        $event->getSubject()->willReturn($channel);
        $enrichedCategoryFeature->isEnabled()->willReturn(true);
        $channel->getCode()->willReturn('deleted_channel_code');

        $cleanCategoryDataLinkedToChannel->__invoke('deleted_channel_code')->shouldBeCalled();

        $this->cleanCategoryData($event);
    }

    function it_does_not_trigger_the_cleaning_of_category_if_subject_is_not_a_channel(
        GenericEvent $event,
        Category $eventSubject,
        CleanCategoryDataLinkedToChannel $cleanCategoryDataLinkedToChannel,
        FeatureFlag $enrichedCategoryFeature
    )
    {
        $event->getSubject()->willReturn($eventSubject);
        $enrichedCategoryFeature->isEnabled()->willReturn(true);

        $cleanCategoryDataLinkedToChannel->__invoke(Argument::any())->shouldNotBeCalled();
    }
}
