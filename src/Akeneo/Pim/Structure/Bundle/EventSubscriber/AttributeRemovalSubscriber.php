<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Bundle\Manager\AttributeCodeBlacklister;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * This subscriber listens on attribute removals, blacklists the removed attribute code
 * and launches the cleaning removed attribute values job when the batch size is reached
 */
class AttributeRemovalSubscriber implements EventSubscriberInterface
{
    private const JOB_NAME = 'clean_removed_attribute_job';
    private const BATCH_SIZE = 1000;

    private array $attributeCodesToClean = [];

    public function __construct(
        private readonly AttributeCodeBlacklister $attributeCodeBlacklister,
        private readonly JobLauncherInterface $jobLauncher,
        private readonly IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_REMOVE => 'blacklistAttributeCodeAndLaunchJob',
        ];
    }

    public function blacklistAttributeCodeAndLaunchJob(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if (!$subject instanceof AttributeInterface) {
            return;
        }

        $attributeCode = $subject->getCode();

        $this->attributeCodesToClean[] = $attributeCode;
        $this->attributeCodeBlacklister->blacklist([$attributeCode]);

        if (count($this->attributeCodesToClean) >= self::BATCH_SIZE) {
            $this->flushEvents();
        }
    }

    public function flushEvents(): void
    {
        if (empty($this->attributeCodesToClean)) {
            return;
        }

        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier(self::JOB_NAME);
        $jobExecution = $this->jobLauncher->launch($jobInstance, $this->tokenStorage->getToken()->getUser(), [
            'attribute_codes' => $this->attributeCodesToClean,
        ]);

        $this->attributeCodeBlacklister->registerJob($this->attributeCodesToClean, $jobExecution->getId());
        $this->attributeCodesToClean = [];
    }
}
