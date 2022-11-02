<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\EventSubscriber;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Event\CompletenessHasBeenUpdated;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Query\CreateJobInstanceInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ComputeCompletenessSubscriber implements EventSubscriberInterface
{
    private array $tableAttributesToCompute = [];

    public function __construct(
        private Connection $connection,
        private JobLauncherInterface $jobLauncher,
        private JobInstanceRepository $jobInstanceRepository,
        private CreateJobInstanceInterface $createJobInstance,
        private TokenStorageInterface $tokenStorage,
        private string $jobName,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CompletenessHasBeenUpdated::class => 'completenessHasBeenUpdated',
            StorageEvents::POST_SAVE => 'launchComputeCompletenessJobIfNeeded'
        ];
    }

    public function completenessHasBeenUpdated(CompletenessHasBeenUpdated $event): void
    {
        $this->tableAttributesToCompute[$event->getAttributeCode()] = true;
    }

    public function launchComputeCompletenessJobIfNeeded(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if (!$subject instanceof AttributeInterface) {
            return;
        }
        if ($subject->getType() !== AttributeTypes::TABLE) {
            return;
        }
        $attributeCode = $subject->getCode();
        if (!isset($this->tableAttributesToCompute[$attributeCode])) {
            return;
        }

        $familyCodes = $this->getRequiredFamilyCodesLinkedToAttributeCode($attributeCode);
        if (\count($familyCodes) === 0) {
            return;
        }

        $configuration = [
            'attribute_code' => $attributeCode,
            'family_codes' => $familyCodes,
        ];
        $user = $this->tokenStorage->getToken()->getUser();
        $this->jobLauncher->launch($this->getOrCreateJobInstance(), $user, $configuration);
        $this->tableAttributesToCompute = [];
    }

    private function getOrCreateJobInstance(): JobInstance
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);
        if (null === $jobInstance) {
            $this->createJobInstance->createJobInstance([
                'code' => $this->jobName,
                'label' => 'Compute completeness of products when their completeness was updated',
                'job_name' => $this->jobName,
                'type' => $this->jobName,
            ]);

            $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);
        }

        return $jobInstance;
    }

    private function getRequiredFamilyCodesLinkedToAttributeCode(string $attributeCode): array
    {
        $query = <<<SQL
            SELECT DISTINCT pim_catalog_family.code from pim_catalog_family
            INNER JOIN pim_catalog_family_attribute on pim_catalog_family.id = pim_catalog_family_attribute.family_id
            INNER JOIN pim_catalog_attribute_requirement on pim_catalog_family_attribute.attribute_id = pim_catalog_attribute_requirement.attribute_id
            INNER JOIN pim_catalog_attribute on pim_catalog_family_attribute.attribute_id =pim_catalog_attribute.id
            WHERE pim_catalog_attribute.code = :attributeCode
            AND required = 1
        SQL;

        return $this->connection->executeQuery(
            $query,
            ['attributeCode' => $attributeCode],
        )->fetchFirstColumn();
    }
}
