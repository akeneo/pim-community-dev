<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\EventSubscriber;

use Akeneo\Category\Domain\Event\AttributeDeactivatedEvent;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CleanCategoryTemplateAttributeAndEnrichedValuesOnAttributeDeactivatedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly JobInstanceRepository $jobInstanceRepository,
        private readonly JobLauncherInterface $jobLauncher,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AttributeDeactivatedEvent::class => 'cleanCategoryDataForAttribute',
        ];
    }

    public function cleanCategoryDataForAttribute(AttributeDeactivatedEvent $event): void
    {
        /** @var JobInstance|null $jobInstance */
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('clean_category_attribute_and_enriched_values');
        if (!$jobInstance instanceof JobInstance) {
            return;
        }

        $this->jobLauncher->launch($jobInstance, $this->tokenStorage->getToken()?->getUser(), [
            'template_uuid' => $event->getTemplateUuid()->getValue(),
            'attribute_uuid' => (string) $event->getAttributeUuid(),
        ]);
    }
}
