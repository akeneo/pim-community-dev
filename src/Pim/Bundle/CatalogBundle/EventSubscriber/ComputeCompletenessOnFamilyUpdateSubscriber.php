<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\FamilyInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Runs a job whenever a family is updated.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComputeCompletenessOnFamilyUpdateSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var JobLauncherInterface */
    private $jobLauncher;

    /** @var IdentifiableObjectRepositoryInterface */
    private $jobInstanceRepository;

    /** @var string */
    private $jobName;

    /**
     * @param TokenStorageInterface                   $tokenStorage
     * @param JobLauncherInterface                    $jobLauncher
     * @param IdentifiableObjectRepositoryInterface   $jobInstanceRepository
     * @param string                                  $jobName
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        JobLauncherInterface $jobLauncher,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        string $jobName
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->jobLauncher = $jobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobName = $jobName;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'computeCompletenessOfProductsFamily',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function computeCompletenessOfProductsFamily(GenericEvent $event): void
    {
        $subject = $event->getSubject();

        if (!$subject instanceof FamilyInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);
        $this->jobLauncher->launch($jobInstance, $user, ['family_code' => $subject->getCode()]);
    }
}
