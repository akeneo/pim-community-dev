<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\AnotherJobStillRunningException;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
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
        private FamilyVariantRepositoryInterface $familyVariantRepository,
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
            $this->ensureNoOtherJobExecutionIsRunning($jobInstance->getId(), $familyVariant->getCode());

            $this->jobLauncher->launch($jobInstance, $user, [
                'family_variant_codes' => [$familyVariant->getCode()]
            ]);
        } catch (AnotherJobStillRunningException $e) {
        }
    }

    private function ensureNoOtherJobExecutionIsRunning(int $jobInstanceId, string $familyVariantCode): void
    {
        $query = <<<SQL
        SELECT EXISTS(
            SELECT *
               FROM akeneo_batch_job_execution abje
               WHERE job_instance_id = :instanceId
                   AND exit_code in ('UNKNOWN', 'EXECUTING')
             AND :familyVariantCode MEMBER OF (JSON_EXTRACT(raw_parameters, '$.family_variant_codes'))
            AND (health_check_time IS NULL OR health_check_time > SUBTIME(UTC_TIMESTAMP(), '0:0:10'))
        );
SQL;
        $stmt = $this->connection->executeQuery(
            $query,
            [
                'instanceId' => $jobInstanceId,
                'familyVariantCode' => $familyVariantCode,
            ]
        );

        if ((int) $stmt->fetchOne() === 0) {
            return;
        }

        $this->logger->warning('Another job execution is still running (id = {job_id})', ['message' => 'another_job_execution_is_still_running', 'job_id' => $stmt[0]['id']]);

        // In case of an old job execution that has not been marked as failed.
        /**if ($jobExecutionRunning->getUpdatedTime() < new \DateTime(self::OUTDATED_JOB_EXECUTION_TIME)) {
            $this->logger->info('Job execution "{job_id}" is outdated: let\'s mark it has failed.', ['message' => 'job_execution_outdated', 'job_id' => $jobExecutionRunning->getId()]);
            $this->executionManager->markAsFailed($jobExecutionRunning->getId());
        }*/

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

        // TODO: change to return false;
        return true;
    }
}
