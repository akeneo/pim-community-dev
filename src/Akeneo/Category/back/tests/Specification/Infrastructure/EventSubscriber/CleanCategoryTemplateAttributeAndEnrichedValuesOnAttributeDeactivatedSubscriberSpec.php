<?php

namespace Specification\Akeneo\Category\Infrastructure\EventSubscriber;

use Akeneo\Category\Domain\Event\AttributeDeactivatedEvent;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Infrastructure\EventSubscriber\CleanCategoryTemplateAttributeAndEnrichedValuesOnAttributeDeactivatedSubscriber;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CleanCategoryTemplateAttributeAndEnrichedValuesOnAttributeDeactivatedSubscriberSpec extends ObjectBehavior
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
        $this->shouldHaveType(CleanCategoryTemplateAttributeAndEnrichedValuesOnAttributeDeactivatedSubscriber::class);
    }

    function it_puts_in_queue_the_job_cleaning_category_after_attribute_deactivation(
        AttributeDeactivatedEvent $event,
        TemplateUuid $templateUuid,
        AttributeUuid $attributeUuid,
        JobInstanceRepository $jobInstanceRepository,
        JobInstance $cleanCategoriesJobInstance,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
    )
    {
        $event->getTemplateUuid()->willReturn($templateUuid);
        $event->getAttributeUuid()->willReturn($attributeUuid);

        $templateUuidValue = '63b7b051-48bb-4084-a427-20ee32933a8c';
        $templateUuid->getValue()->willReturn($templateUuidValue);
        $attributeUuidValue = 'c87c8b3c-5642-425c-a3b7-8dd5bc503e67';
        $attributeUuid->getValue()->willReturn($attributeUuidValue);
        $jobInstanceRepository->findOneByIdentifier('clean_category_attribute_and_enriched_values')->willReturn($cleanCategoriesJobInstance);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $jobLauncher->launch(
            $cleanCategoriesJobInstance,
            $user,
            [
                'template_uuid' => $templateUuidValue,
                'attribute_uuid' => $attributeUuidValue,
            ]

        )->shouldBeCalled();

        $this->cleanCategoryDataForAttribute($event);
    }
}
