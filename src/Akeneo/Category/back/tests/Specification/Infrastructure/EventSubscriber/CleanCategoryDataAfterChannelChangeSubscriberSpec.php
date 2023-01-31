<?php

namespace Specification\Akeneo\Category\Infrastructure\EventSubscriber;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Infrastructure\EventSubscriber\CleanCategoryDataAfterChannelChangeSubscriber;
use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CleanCategoryDataAfterChannelChangeSubscriberSpec extends ObjectBehavior
{
    function let(
        FeatureFlag $enrichedCategoryFeature,
        JobInstanceRepository $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage,
    )
    {
        $this->beConstructedWith(
            $enrichedCategoryFeature,
            $jobInstanceRepository,
            $jobLauncher,
            $tokenStorage,
        );
    }

    function it_is_initializable()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
        $this->shouldHaveType(CleanCategoryDataAfterChannelChangeSubscriber::class);
    }

    function it_puts_in_queue_the_job_cleaning_category_after_channel_removal(
        GenericEvent $event,
        Channel $channel,
        FeatureFlag $enrichedCategoryFeature,
        JobInstanceRepository $jobInstanceRepository,
        JobInstance $cleanCategoriesJobInstance,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
    )
    {
        $event->getSubject()->willReturn($channel);
        $enrichedCategoryFeature->isEnabled()->willReturn(true);
        $channel->getCode()->willReturn('deleted_channel_code');
        $jobInstanceRepository->findOneByIdentifier('clean_categories_enriched_values')->willReturn($cleanCategoriesJobInstance);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $jobLauncher->launch(
            $cleanCategoriesJobInstance,
            $user,
            ['channel_code' => 'deleted_channel_code']
        )->shouldBeCalled();

        $this->cleanCategoryData($event);
    }

    function it_does_not_puts_in_queue_the_job_cleaning_category_if_subject_is_not_a_channel(
        GenericEvent $event,
        Category $eventSubject,
        FeatureFlag $enrichedCategoryFeature,
        JobInstanceRepository $jobInstanceRepository,
    )
    {
        $event->getSubject()->willReturn($eventSubject);
        $enrichedCategoryFeature->isEnabled()->willReturn(true);

        $jobInstanceRepository->findOneByIdentifier('clean_categories_enriched_values')->shouldNotBeCalled();
    }

    function it_does_not_puts_in_queue_the_job_cleaning_category_if_feature_flag_is_deactivated(
        GenericEvent $event,
        Channel $eventSubject,
        FeatureFlag $enrichedCategoryFeature,
        JobInstanceRepository $jobInstanceRepository,
    )
    {
        $event->getSubject()->willReturn($eventSubject);
        $enrichedCategoryFeature->isEnabled()->willReturn(false);

        $jobInstanceRepository->findOneByIdentifier('clean_categories_enriched_values')->shouldNotBeCalled();
    }
}
