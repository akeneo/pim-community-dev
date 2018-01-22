<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * When a family variant is saved, we need to update all product models and variant products that belong
 * to this family variant because the structure of the family variant could have changed.
 *
 * So we may need to remove values or move values from some level to another.
 *
 * We make sure we do not run this job for new family variants.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ComputeFamilyVariantStructureChangesSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var JobLauncherInterface */
    private $jobLauncher;

    /** @var IdentifiableObjectRepositoryInterface */
    private $jobInstanceRepository;

    /** @var string */
    private $jobName;

    /** @var bool */
    private $isFamilyVariantNew;

    /**
     * @param TokenStorageInterface                 $tokenStorage
     * @param JobLauncherInterface                  $jobLauncher
     * @param IdentifiableObjectRepositoryInterface $jobInstanceRepository
     * @param string                                $jobName
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
        $this->isFamilyVariantNew = true;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE => 'checkIsFamilyVariantNew',
            StorageEvents::POST_SAVE => 'computeVariantStructureChanges',
        ];
    }

    /**
     * Method that checks if the given family variant is new. This information is then used in the
     * `computeVariantStructureChanges` function to determine wether a job should be launched or not.
     *
     * @param GenericEvent $event
     */
    public function checkIsFamilyVariantNew(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if (!$subject instanceof FamilyVariantInterface) {
            return;
        }

        $this->isFamilyVariantNew = $this->isFamilyVariantNew($subject);
    }

    /**
     * @param GenericEvent $event
     */
    public function computeVariantStructureChanges(GenericEvent $event): void
    {
        $familyVariant = $event->getSubject();
        if (!$familyVariant instanceof FamilyVariantInterface) {
            return;
        }

        if ($this->isFamilyVariantNew) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);

        $this->jobLauncher->launch($jobInstance, $user, [
            'family_variant_codes' => [$familyVariant->getCode()]
        ]);
    }

    /**
     * Checks if the given family variant is new.
     *
     * @param FamilyVariantInterface $familyVariant
     *
     * @return bool
     */
    private function isFamilyVariantNew(FamilyVariantInterface $familyVariant): bool
    {
        return null === $familyVariant->getId();
    }
}
