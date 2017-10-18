<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\AttributeRequirementRepositoryInterface;
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

    /** @var AttributeRequirementRepositoryInterface */
    private $attributeRequirementRepository;

    /** @var array */
    private $areAttributeRequirementsUpdatedForFamilies;

    /**
     * @param TokenStorageInterface                   $tokenStorage
     * @param JobLauncherInterface                    $jobLauncher
     * @param IdentifiableObjectRepositoryInterface   $jobInstanceRepository
     * @param AttributeRequirementRepositoryInterface $attributeRequirementRepository
     * @param string                                  $jobName
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        JobLauncherInterface $jobLauncher,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        AttributeRequirementRepositoryInterface $attributeRequirementRepository,
        string $jobName
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->jobLauncher = $jobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->attributeRequirementRepository = $attributeRequirementRepository;
        $this->jobName = $jobName;
        $this->areAttributeRequirementsUpdatedForFamilies = [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE  => 'areAttributeRequirementsUpdated',
            StorageEvents::POST_SAVE => 'computeCompletenessOfProductsFamily',
        ];
    }

    /**
     * Defines whether the computation of the completenesses for products belonging to this family should be done in the
     * POST_SAVE event.
     *
     * It does not recompute completenesses if:
     * - We are creating a new family
     * - Attribute requirements of the family did not change
     *
     * @param GenericEvent $event
     */
    public function areAttributeRequirementsUpdated(GenericEvent $event): void
    {
        $subject = $event->getSubject();

        if (!$subject instanceof FamilyInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        if (null === $subject->getId()) {
            $this->areAttributeRequirementsUpdatedForFamilies[$subject->getCode()] = false;

            return;
        }

        $oldAttributeRequirementsKeys = $this->getOldAttributeRequirementKeys($subject);
        $newAttributeRequirementsKeys = array_keys($subject->getAttributeRequirements());

        $this->areAttributeRequirementsUpdatedForFamilies[$subject->getCode()] =
            $this->areAttributeRequirementsListsDifferent($oldAttributeRequirementsKeys, $newAttributeRequirementsKeys);
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

        if ($this->areAttributeRequirementsUpdatedForFamilies[$subject->getCode()]) {
            $user = $this->tokenStorage->getToken()->getUser();
            $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);
            $this->jobLauncher->launch($jobInstance, $user, ['family_code' => $subject->getCode()]);
        }

        unset($this->areAttributeRequirementsUpdatedForFamilies[$subject->getCode()]);
    }

    /**
     * @param FamilyInterface $family
     *
     * @return array
     */
    private function getOldAttributeRequirementKeys(FamilyInterface $family): array
    {
        $oldAttributeRequirementsKeys = [];

        $oldAttributeRequirements = $this->attributeRequirementRepository->findRequiredAttributesCodesByFamily($family);
        foreach ($oldAttributeRequirements as $oldAttributeRequirement) {
            $oldAttributeRequirementsKeys[] =
                $oldAttributeRequirement['attribute'] . '_' . $oldAttributeRequirement['channel'];
        }

        return $oldAttributeRequirementsKeys;
    }

    /**
     * @param $oldAttributeRequirementsKeys
     * @param $newAttributeRequirementsKeys
     *
     * @return bool
     */
    private function areAttributeRequirementsListsDifferent(
        $oldAttributeRequirementsKeys,
        $newAttributeRequirementsKeys
    ): bool {
        sort($oldAttributeRequirementsKeys);
        sort($newAttributeRequirementsKeys);

        $diff = array_merge(
            array_diff($oldAttributeRequirementsKeys, $newAttributeRequirementsKeys),
            array_diff($newAttributeRequirementsKeys, $oldAttributeRequirementsKeys)
        );

        return count($diff) > 0;
    }
}
