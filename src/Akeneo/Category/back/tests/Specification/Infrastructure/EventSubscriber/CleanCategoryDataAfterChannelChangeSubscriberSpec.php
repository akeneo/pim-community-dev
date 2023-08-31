<?php

namespace Specification\Akeneo\Category\Infrastructure\EventSubscriber;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Infrastructure\EventSubscriber\CleanCategoryDataAfterChannelChangeSubscriber;
use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CleanCategoryDataAfterChannelChangeSubscriberSpec extends ObjectBehavior
{
    function let(
        JobInstanceRepository $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage,
    )
    {
        $this->beConstructedWith(
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
        JobInstanceRepository $jobInstanceRepository,
        JobInstance $cleanCategoriesJobInstance,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
    )
    {
        $event->getSubject()->willReturn($channel);
        $channel->getCode()->willReturn('deleted_channel_code');
        $channel->getLocales()->willReturn(new ArrayCollection([]));
        $jobInstanceRepository->findOneByIdentifier('clean_categories_enriched_values')->willReturn($cleanCategoriesJobInstance);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $jobLauncher->launch(
            $cleanCategoriesJobInstance,
            $user,
            [
                'channel_code' => 'deleted_channel_code',
                'locales_codes' => [],
            ]

        )->shouldBeCalled();

        $this->cleanCategoryDataForChannelLocale($event);
    }

    function it_puts_in_queue_the_job_cleaning_category_after_channel_update(
        GenericEvent $event,
        Channel $channel,
        JobInstanceRepository $jobInstanceRepository,
        JobInstance $cleanCategoriesJobInstance,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
        Locale $locale,
        ArrayCollection $localesCollection,
    )
    {
        $event->getSubject()->willReturn($channel);
        $channel->getCode()->willReturn('deleted_channel_code');
        $locale->getCode()->willReturn('en_US');
        $localesCollection->getValues()->willReturn([$locale]);
        $channel->getLocales()->willReturn($localesCollection);
        $jobInstanceRepository->findOneByIdentifier('clean_categories_enriched_values')->willReturn($cleanCategoriesJobInstance);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $jobLauncher->launch(
            $cleanCategoriesJobInstance,
            $user,
            [
                'channel_code' => 'deleted_channel_code',
                'locales_codes' => ['en_US'],
            ]

        )->shouldBeCalled();

        $this->cleanCategoryDataForChannelLocale($event);
    }

    function it_does_not_puts_in_queue_the_job_cleaning_category_if_subject_is_not_a_channel(
        GenericEvent $event,
        Category $eventSubject,
        JobInstanceRepository $jobInstanceRepository,
    )
    {
        $event->getSubject()->willReturn($eventSubject);

        $jobInstanceRepository->findOneByIdentifier('clean_categories_enriched_values')->shouldNotBeCalled();
    }

    function it_does_not_puts_in_queue_the_job_cleaning_category_if_feature_flag_is_deactivated(
        GenericEvent $event,
        Channel $eventSubject,
        JobInstanceRepository $jobInstanceRepository,
    )
    {
        $event->getSubject()->willReturn($eventSubject);

        $jobInstanceRepository->findOneByIdentifier('clean_categories_enriched_values')->shouldNotBeCalled();
    }
}
