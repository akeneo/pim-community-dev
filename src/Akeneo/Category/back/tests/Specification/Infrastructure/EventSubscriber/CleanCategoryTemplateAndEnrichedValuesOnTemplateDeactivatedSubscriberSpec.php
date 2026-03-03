<?php

namespace Specification\Akeneo\Category\Infrastructure\EventSubscriber;

use Akeneo\Category\Domain\Event\TemplateDeactivatedEvent;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Infrastructure\EventSubscriber\CleanCategoryTemplateAndEnrichedValuesOnTemplateDeactivatedSubscriber;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CleanCategoryTemplateAndEnrichedValuesOnTemplateDeactivatedSubscriberSpec extends ObjectBehavior
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
        $this->shouldHaveType(CleanCategoryTemplateAndEnrichedValuesOnTemplateDeactivatedSubscriber::class);
    }

    function it_puts_in_queue_the_job_cleaning_category_after_template_deactivation(
        TemplateDeactivatedEvent $event,
        TemplateUuid $templateUuid,
        JobInstanceRepository $jobInstanceRepository,
        JobInstance $cleanCategoriesJobInstance,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
    )
    {
        $event->getTemplateUuid()->willReturn($templateUuid);
        $templateUuid->getValue()->willReturn('63b7b051-48bb-4084-a427-20ee32933a8c');
        $jobInstanceRepository->findOneByIdentifier('clean_category_template_and_enriched_values')->willReturn($cleanCategoriesJobInstance);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $jobLauncher->launch(
            $cleanCategoriesJobInstance,
            $user,
            [
                'template_uuid' => '63b7b051-48bb-4084-a427-20ee32933a8c',
            ]

        )->shouldBeCalled();

        $this->cleanCategoryDataForTemplate($event);
    }
}
