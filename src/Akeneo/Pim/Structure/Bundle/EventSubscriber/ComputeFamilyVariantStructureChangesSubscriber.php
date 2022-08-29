<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
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
 * The job is not launched if:
 *  - no changes on level's attribute list
 *  - no changes in the axes
 *  - a previous job concerning this family variant is about to start
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ComputeFamilyVariantStructureChangesSubscriber implements EventSubscriberInterface
{
    public const DISABLE_JOB_LAUNCHING = 'DISABLE_COMPUTE_FAMILY_VARIANT_STRUCTURE_CHANGES_LAUNCHING';

    /** @var array<string, bool> */
    private array $isFamilyVariantNew = [];

    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private JobLauncherInterface $jobLauncher,
        private IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        private Connection $connection,
        private LoggerInterface $logger,
        private string $jobName
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE => 'recordIsNewFamilyVariant',
            StorageEvents::POST_SAVE => 'computeVariantStructureChanges',
            StorageEvents::POST_SAVE_ALL => 'bulkComputeVariantStructureChanges',
        ];
    }

    public function recordIsNewFamilyVariant(GenericEvent $event): void
    {
        $familyVariant = $event->getSubject();
        if (!$familyVariant instanceof FamilyVariantInterface) {
            return;
        }

        $this->isFamilyVariantNew[$familyVariant->getCode()] = null === $familyVariant->getId();
    }

    public function computeVariantStructureChanges(GenericEvent $event): void
    {
        $familyVariant = $event->getSubject();
        if (!$familyVariant instanceof FamilyVariantInterface) {
            return;
        }

        if (
            !$event->hasArgument('unitary') || false === $event->getArgument('unitary')
            || ($event->hasArgument('is_new') && $event->getArgument('is_new'))
            || ($event->hasArgument(self::DISABLE_JOB_LAUNCHING) && $event->getArgument(self::DISABLE_JOB_LAUNCHING))
            || (!$this->variantAttributeSetOfFamilyVariantIsUpdated($familyVariant))
        ) {
            return;
        }

        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);
        if ($this->noOtherJobExecutionIsPending($jobInstance->getId(), $familyVariant->getCode())) {
            $user = $this->tokenStorage->getToken()->getUser();
            $this->jobLauncher->launch($jobInstance, $user, ['family_variant_codes' => [$familyVariant->getCode()]]);
        }
    }

    public function bulkComputeVariantStructureChanges(GenericEvent $event): void
    {
        $familyVariants = $event->getSubject();
        if (!is_array($familyVariants)
            || [] === $familyVariants
            || !current($familyVariants) instanceof FamilyVariantInterface
            || ($event->hasArgument(self::DISABLE_JOB_LAUNCHING) && $event->getArgument(self::DISABLE_JOB_LAUNCHING))
        ) {
            return;
        }

        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);
        $familyVariantCodesToCompute = \array_values(\array_map(
            static fn (FamilyVariantInterface $familyVariant): string => $familyVariant->getCode(),
            \array_filter(
                $familyVariants,
                fn (FamilyVariantInterface $familyVariant): bool =>
                    !($this->isFamilyVariantNew[$familyVariant->getCode()] ?? false)
                    && ($this->variantAttributeSetOfFamilyVariantIsUpdated($familyVariant))
                    && $this->noOtherJobExecutionIsPending($jobInstance->getId(), $familyVariant->getCode())
            )
        ));

        $this->isFamilyVariantNew = [];
        if ([] === $familyVariantCodesToCompute) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $this->jobLauncher->launch($jobInstance, $user, ['family_variant_codes' => $familyVariantCodesToCompute]);
    }

    private function noOtherJobExecutionIsPending(int $jobInstanceId, string $familyVariantCode): bool
    {
        /**
         * status 2 = STARTING
         * The check on the create_time is a security in case we have ghost job that are never started.
         */
        $query = <<<SQL
        SELECT id
        FROM akeneo_batch_job_execution abje
        WHERE job_instance_id = :instanceId
            AND status = 2
            AND :familyVariantCode MEMBER OF (JSON_EXTRACT(raw_parameters, '$.family_variant_codes'))
            AND create_time > DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 DAY)
        ORDER BY id DESC
        LIMIT 1
        SQL;
        $jobId = $this->connection->executeQuery(
            $query,
            ['instanceId' => $jobInstanceId, 'familyVariantCode' => $familyVariantCode]
        )->fetchOne();

        if (false === $jobId) {
            return true;
        }

        $this->logger->notice(
            'ComputeFamilyVariantStructureChangesSubscriber: Another job execution is still running (id = {job_id})',
            ['message' => 'another_job_execution_is_still_running', 'job_id' => $jobId]
        );

        return false;
    }

    private function variantAttributeSetOfFamilyVariantIsUpdated(FamilyVariantInterface $familyVariant): bool
    {
        // Warning: releaseEvents can be called only once by family variant (events are cleared after the first call)
        $events = $familyVariant->releaseEvents();

        return \in_array(FamilyVariantInterface::AXES_WERE_UPDATED_ON_LEVEL, $events)
            || \in_array(FamilyVariantInterface::ATTRIBUTES_WERE_UPDATED_ON_LEVEL, $events);
    }
}
