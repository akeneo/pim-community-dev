<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\AnotherJobStillRunningException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
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
    private string $jobName;
    private ?FamilyVariantInterface $previousFamilyVariant;

    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private JobLauncherInterface $jobLauncher,
        private IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        private Connection $connection,
        private LoggerInterface $logger,
        private IdentifiableObjectRepositoryInterface $familyVariantRepository,
        string $jobName
    ) {
        $this->jobName = $jobName;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE => 'getPreviousValue',
            StorageEvents::POST_SAVE => 'computeVariantStructureChanges',
        ];
    }

    public function getPreviousValue(GenericEvent $event): void
    {
        $familyVariant = $event->getSubject();
        if (!$familyVariant instanceof FamilyVariantInterface) {
            return;
        }
        $this->previousFamilyVariant = $this->familyVariantRepository->findOneByIdentifier($familyVariant->getCode());
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

        $hasChanged = $this->hasFamilyVariantStructureBeenUpdated($familyVariant);
//        $levels = $familyVariant->getNumberOfLevel();
//        $isDirty = false;
//        for ($i = 1; $i <= $levels; $i++) {
//            $variantAttributeSets = $familyVariant->getVariantAttributeSet($i);
//            if (!$isDirty && ($variantAttributeSets->getAttributes()->isDirty() || $variantAttributeSets->getAxes()->isDirty())) {
//                $isDirty = true;
//            }
//        }
//        if (!$isDirty && $familyVariant->getTranslations()->isDirty()) {
//            $isDirty = $familyVariant->getTranslations()->isDirty();
//        }
//
//        if (!$isDirty) {
//            return;
//        }
////        $isDirty = $familyVariant['variantAttributeSets']['isDirty'];

        if ($event->getArgument('is_new') || (!$event->getArgument('is_new') && !$hasChanged)) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);

        try {
            $this->ensureNoOtherJobExecutionIsRunning($jobInstance, $familyVariant);

            $this->jobLauncher->launch($jobInstance, $user, [
                'family_variant_codes' => [$familyVariant->getCode()]
            ]);
        } catch (AnotherJobStillRunningException $e) {
        }
    }

    private function ensureNoOtherJobExecutionIsRunning(JobInstance $jobInstance, FamilyVariantInterface $familyVariant): void
    {
        $query = <<<SQL
        SELECT *
        FROM akeneo_batch_job_execution abje,
         JSON_TABLE(JSON_EXTRACT(abje.raw_parameters, '$.family_variant_codes'), '$[*]' COLUMNS (
            `code` VARCHAR(100) PATH '$'
             )) pmd_attribute_codes
    WHERE job_instance_id = :instanceId
        AND exit_code in ('UNKNOWN', 'EXECUTED')
        AND code = :familyVariantCode;
SQL;
        $stmt = $this->connection->executeQuery(
            $query,
            [
                'instanceId' => $jobInstance->getId(),
                'familyVariantCode' => $familyVariant->getCode(),
            ]
        )->fetchAllAssociative();

        if (\count($stmt) === 0) {
            return;
        }

        $this->logger->warning('Another job execution is still running (id = {job_id})', ['message' => 'another_job_execution_is_still_running', 'job_id' => $stmt[0]['id']]);
        // TODO: should we stop job execution kill when longer than 8h ?

        throw new AnotherJobStillRunningException();
    }

    private function hasFamilyVariantStructureBeenUpdated(FamilyVariantInterface $familyVariant): bool
    {
//        $previousFamilyVariant = $this->familyVariantRepository->findOneByIdentifier($familyVariant->getCode());
        $levels = $familyVariant->getNumberOfLevel();

        for ($i = 1; $i <= $levels; $i++) {
            $variantAttributeSets = $familyVariant->getVariantAttributeSet($i)->getAttributes()->getValues();
            $formerVariantAttributeSets = $this->previousFamilyVariant?->getVariantAttributeSet($i)?->getAttributes()?->getValues() ?? [];

            $formerAttributeSetCodes = \array_map(fn(Attribute $attribute) => $attribute->getCode(), $formerVariantAttributeSets);
            $newAttributeSetCodes = \array_map(fn(Attribute $attribute) => $attribute->getCode(), $variantAttributeSets);

            $hasChanges = \array_diff($formerAttributeSetCodes, $newAttributeSetCodes);

            if (\count($hasChanges) > 0) {
                return true;
            }
        }

        return false;
    }
}
