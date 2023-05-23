<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Bundle\Manager\AttributeCodeBlacklister;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpKernel\KernelEvents;
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
    private bool $terminateEventIsRegistered = false;

    public function __construct(
        private AttributeCodeBlacklister $attributeCodeBlacklister,
        private JobLauncherInterface $jobLauncher,
        private IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        private TokenStorageInterface $tokenStorage,
        private EventDispatcherInterface $eventDispatcher
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

        $this->registerCleanAttributeJobOnTerminate();

        $attributeCode = $subject->getCode();
        $this->attributeCodesToClean[] = $attributeCode;
        $this->attributeCodeBlacklister->blacklist([$attributeCode]);

        if (count($this->attributeCodesToClean) >= self::BATCH_SIZE) {
            $this->launchCleanAttributeJob();
        }
    }

    public function launchCleanAttributeJob(): void
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

    private function registerCleanAttributeJobOnTerminate()
    {
        if (!$this->terminateEventIsRegistered) {
            $this->eventDispatcher->addListener(KernelEvents::TERMINATE, [$this, 'launchCleanAttributeJob']);
            $this->eventDispatcher->addListener(ConsoleEvents::TERMINATE, [$this, 'launchCleanAttributeJob']);
            $this->terminateEventIsRegistered = true;
        }
    }
}
