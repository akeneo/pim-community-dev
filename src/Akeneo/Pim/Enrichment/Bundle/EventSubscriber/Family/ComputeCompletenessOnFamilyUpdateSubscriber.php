<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Family;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Family\FindAttributesForFamily;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRequirementRepositoryInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
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
final class ComputeCompletenessOnFamilyUpdateSubscriber implements EventSubscriberInterface
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

    /** @var FindAttributesForFamily */
    private $findAttributesForFamily;

    /** @var bool */
    private $areAttributeRequirementsUpdatedForFamilies;

    /** @var bool */
    private $isAttributeListUpdated;

    /**
     * @param TokenStorageInterface                   $tokenStorage
     * @param JobLauncherInterface                    $jobLauncher
     * @param IdentifiableObjectRepositoryInterface   $jobInstanceRepository
     * @param AttributeRequirementRepositoryInterface $attributeRequirementRepository
     * @param FindAttributesForFamily                 $findAttributesForFamily
     * @param string                                  $jobName
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        JobLauncherInterface $jobLauncher,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        AttributeRequirementRepositoryInterface $attributeRequirementRepository,
        string $jobName,
        FindAttributesForFamily $findAttributesForFamily
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->jobLauncher = $jobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->attributeRequirementRepository = $attributeRequirementRepository;
        $this->findAttributesForFamily = $findAttributesForFamily;
        $this->jobName = $jobName;
        $this->areAttributeRequirementsUpdatedForFamilies = false;
        $this->isAttributeListUpdated = false;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE  => 'checkIfUpdateNeedsToRunBackgroundJob',
            StorageEvents::POST_SAVE => 'computeCompletenessOfProductsFamily',
        ];
    }

    /**
     * Defines whether the computation of the completenesses for products belonging to this family should be done in the
     * POST_SAVE event.
     *
     * It needs to recompute completenesses or reindex products only if:
     * - Attribute requirements of the family changed
     * - Attributes list of the family changed (to reindex empty values for the operator IS_EMPTY)
     *
     * @param GenericEvent $event
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function checkIfUpdateNeedsToRunBackgroundJob(GenericEvent $event): void
    {
        $subject = $event->getSubject();

        if (!$subject instanceof FamilyInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        if (null === $subject->getId()) {
            $this->areAttributeRequirementsUpdatedForFamilies = false;
            $this->isAttributeListUpdated = false;

            return;
        }

        $this->areAttributeRequirementsUpdatedForFamilies = $this->areAttributeRequirementsListsUpdated($subject);
        $this->isAttributeListUpdated = $this->isAttributeListUpdated($subject);
    }

    /**
     * @param GenericEvent $event
     *
     * @throws \InvalidArgumentException
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

        if ($this->areAttributeRequirementsUpdatedForFamilies || $this->isAttributeListUpdated) {
            $token = $this->tokenStorage->getToken();
            $user = $token->getUser();
            $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);
            $this->jobLauncher->launch($jobInstance, $user, ['family_code' => $subject->getCode()]);
        }
    }

    /**
     * @param FamilyInterface $family
     *
     * @return bool
     */
    private function areAttributeRequirementsListsUpdated(FamilyInterface $family): bool
    {
        $oldAttributeRequirementsKeys = $this->getOldAttributeRequirementKeys($family);
        $newAttributeRequirementsKeys = array_keys($family->getAttributeRequirements());

        sort($oldAttributeRequirementsKeys);
        sort($newAttributeRequirementsKeys);

        $diff = array_merge(
            array_diff($oldAttributeRequirementsKeys, $newAttributeRequirementsKeys),
            array_diff($newAttributeRequirementsKeys, $oldAttributeRequirementsKeys)
        );

        return count($diff) > 0;
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
            $oldAttributeRequirementsKeys[] = sprintf(
                '%s_%s',
                $oldAttributeRequirement['attribute'],
                $oldAttributeRequirement['channel']
            );
        }

        return $oldAttributeRequirementsKeys;
    }

    /**
     * @param FamilyInterface $family
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return bool
     */
    private function isAttributeListUpdated(FamilyInterface $family): bool
    {
        $oldAttributeList = $this->findAttributesForFamily->execute($family);
        $newAttributeList = $family->getAttributes()->map(function (AttributeInterface $attribute) {
            return $attribute->getCode();
        })->toArray();

        sort($oldAttributeList);
        sort($newAttributeList);

        $diff = array_merge(
            array_diff($oldAttributeList, $newAttributeList),
            array_diff($newAttributeList, $oldAttributeList)
        );

        return count($diff) > 0;
    }
}
