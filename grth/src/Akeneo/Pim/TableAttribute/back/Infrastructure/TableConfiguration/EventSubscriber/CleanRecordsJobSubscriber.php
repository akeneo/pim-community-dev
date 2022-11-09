<?php

declare(strict_types=1);

namespace Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\EventSubscriber;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\GetColumnsLinkedToAReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Event\RecordsDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Query\CreateJobInstanceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CleanRecordsJobSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private JobInstanceRepository $jobInstanceRepository,
        private JobLauncherInterface $jobLauncher,
        private CreateJobInstanceInterface $createJobInstance,
        private GetColumnsLinkedToAReferenceEntity $getColumnsLinkedToAReferenceEntity,
        private string $jobName
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RecordsDeletedEvent::class => 'whenRecordsAreDeleted',
        ];
    }

    public function whenRecordsAreDeleted(RecordsDeletedEvent $recordsDeletedEvent): void
    {
        $this->launchJobs(
            $recordsDeletedEvent->getReferenceEntityIdentifier()->normalize(),
            array_map(
                static fn (RecordCode $recordCode): string => $recordCode->normalize(),
                $recordsDeletedEvent->getRecordCodes()
            )
        );
    }

    private function launchJobs(string $referenceEntityIdentifier, array $recordCodes): void
    {
        $columns = $this->getColumnsLinkedToAReferenceEntity->forIdentifier($referenceEntityIdentifier);

        $jobInstance = null;

        $columnsByAttribute = [];

        foreach ($columns as $column) {
            $columnsByAttribute[$column['attribute_code']][] = $column['column_code'];
        }

        foreach ($columnsByAttribute as $attributeCode => $columnCodes) {
            $configuration = [
                'attribute_code' => $attributeCode,
                'removed_options_per_column_code' => [],
            ];

            foreach ($columnCodes as $columnCode) {
                $configuration['removed_options_per_column_code'][$columnCode] = $recordCodes;
            }

            $user = $this->tokenStorage->getToken()->getUser();

            if ($jobInstance === null) {
                $jobInstance = $this->getOrCreateJobInstance();
            }
            $this->jobLauncher->launch($jobInstance, $user, $configuration);
        }
    }

    private function getOrCreateJobInstance(): JobInstance
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);
        if (null === $jobInstance) {
            $this->createJobInstance->createJobInstance([
                'code' => $this->jobName,
                'label' => 'Remove the non existing values from product and product models table attribute',
                'job_name' => $this->jobName,
                'type' => $this->jobName,
            ]);

            $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);
        }

        return $jobInstance;
    }
}
