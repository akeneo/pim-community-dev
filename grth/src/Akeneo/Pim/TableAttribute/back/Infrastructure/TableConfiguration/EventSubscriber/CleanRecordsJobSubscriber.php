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

use Akeneo\ReferenceEntity\Domain\Event\RecordDeletedEvent;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Query\CreateJobInstanceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CleanRecordsJobSubscriber implements EventSubscriberInterface
{
    private JobLauncherInterface $jobLauncher;
    private JobInstanceRepository $jobInstanceRepository;
    private TokenStorageInterface $tokenStorage;
    private CreateJobInstanceInterface $createJobInstance;
    private string $jobName;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        JobInstanceRepository $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        CreateJobInstanceInterface $createJobInstance,
        string $jobName
    ) {
        $this->jobLauncher = $jobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->tokenStorage = $tokenStorage;
        $this->createJobInstance = $createJobInstance;
        $this->jobName = $jobName;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RecordDeletedEvent::class => 'whenRecordDeleted'
        ];
    }

    public function whenRecordDeleted(RecordDeletedEvent $recordDeletedEvent): void
    {
        // @todo: validation ?

        $configuration = [
            $recordDeletedEvent::class => [
                'clean_record_reference_entity_identifier' => $recordDeletedEvent->getReferenceEntityIdentifier()->normalize(), //todo brand
                'clean_record_record_code' => $recordDeletedEvent->getRecordCode(), //todo Alessi
            ]
        ];

        $user = $this->tokenStorage->getToken()->getUser();

        $this->jobLauncher->launch($this->getOrCreateJobInstance(), $user, $configuration);
    }

    private function getOrCreateJobInstance(): JobInstance
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);
        if (null === $jobInstance) {
            $this->createJobInstance->createJobInstance([
                'code' => $this->jobName,
                'label' => 'Remove the non existing table option values from product and product models',
// @todo: one description for the job here and in CleanOptionsJobSubscriber ???
//                'label' => 'Remove the non existing record values from product and product models table attribute',
                'job_name' => $this->jobName,
                'type' => $this->jobName,
            ]);

            $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);
        }

        return $jobInstance;
    }
}
