<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Bundle\Manager\AttributeCodeBlacklister;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * This subscriber listens on attribute removals, blacklists the removed attribute code
 * and launches the cleaning removed attribute values job
 */
class AttributeRemovalSubscriber implements EventSubscriberInterface
{
    private const JOB_NAME = 'clean_removed_attribute_job';

    private AttributeCodeBlacklister $attributeCodeBlacklister;
    private JobLauncherInterface $jobLauncher;
    private IdentifiableObjectRepositoryInterface $jobInstanceRepository;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        AttributeCodeBlacklister $attributeCodeBlacklister,
        JobLauncherInterface $jobLauncher,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->attributeCodeBlacklister = $attributeCodeBlacklister;
        $this->jobLauncher = $jobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents()
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
        $this->attributeCodeBlacklister->blacklist($attributeCode);

        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier(self::JOB_NAME);
        $jobExecution = $this->jobLauncher->launch($jobInstance, $this->tokenStorage->getToken()->getUser(), [
            'attribute_codes' => [$attributeCode]
        ]);

        $this->attributeCodeBlacklister->registerJob($attributeCode, $jobExecution->getId());
    }
}
