<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\EventSubscriber;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\GetColumnsLinkedToAReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Event\RecordsDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\Test\Pim\TableAttribute\Helper\FeatureHelper;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Query\CreateJobInstanceInterface;
use Akeneo\UserManagement\Bundle\Security\SystemUserToken;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CleanRecordsJobSubscriberSpec extends ObjectBehavior
{
    function let(
        TokenStorageInterface $tokenStorage,
        JobInstanceRepository $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        CreateJobInstanceInterface $createJobInstance,
        GetColumnsLinkedToAReferenceEntity $getColumnsLinkedToAReferenceEntity
    ) {
        FeatureHelper::skipSpecTestWhenReferenceEntityIsNotActivated();

        $this->beConstructedWith(
            $tokenStorage,
            $jobInstanceRepository,
            $jobLauncher,
            $createJobInstance,
            $getColumnsLinkedToAReferenceEntity,
            'jobName'
        );
    }

    function it_does_nothing_when_no_columns_are_linked_to_the_reference_entity_with_bulk_event(
        JobLauncherInterface $jobLauncher,
        GetColumnsLinkedToAReferenceEntity $getColumnsLinkedToAReferenceEntity
    ) {
        $recordsDeletedEvent = new RecordsDeletedEvent(
            [RecordIdentifier::create('id', 'code', 'fingerprint')],
            [RecordCode::fromString('code')],
            ReferenceEntityIdentifier::fromString('id')
        );

        $getColumnsLinkedToAReferenceEntity->forIdentifier('id')->willReturn([]);

        $jobLauncher->launch(Argument::cetera())->shouldNotBeCalled();
        $this->whenRecordsAreDeleted($recordsDeletedEvent);
    }

    function it_launches_a_job_with_bulk_event(
        JobLauncherInterface $jobLauncher,
        GetColumnsLinkedToAReferenceEntity $getColumnsLinkedToAReferenceEntity,
        TokenStorageInterface $tokenStorage,
        JobInstanceRepository $jobInstanceRepository,
        JobInstance $jobInstance
    ) {
        $recordsDeletedEvent = new RecordsDeletedEvent(
            [RecordIdentifier::create('id', 'code', 'fingerprint')],
            [RecordCode::fromString('code')],
            ReferenceEntityIdentifier::fromString('id')
        );

        $getColumnsLinkedToAReferenceEntity->forIdentifier('id')->willReturn(
            [
                ['attribute_code' => 'attribute_code', 'column_code' => 'column_code'],
                ['attribute_code' => 'attribute_code_2', 'column_code' => 'column_code_2'],
            ]
        );
        $user = new User();
        $tokenStorage->getToken()->willReturn(new SystemUserToken($user));
        $jobInstanceRepository->findOneByIdentifier('jobName')->willReturn($jobInstance);
        $jobLauncher->launch(
            $jobInstance,
            $user,
            [
                'attribute_code' => 'attribute_code',
                'removed_options_per_column_code' => [
                    'column_code' => ['code'],
                ],
            ]
        )->shouldBeCalledOnce();
        $jobLauncher->launch(
            $jobInstance,
            $user,
            [
                'attribute_code' => 'attribute_code_2',
                'removed_options_per_column_code' => [
                    'column_code_2' => ['code'],
                ],
            ]
        )->shouldBeCalledOnce();
        $this->whenRecordsAreDeleted($recordsDeletedEvent);
    }

    function it_creates_a_new_job_and_launches_it(
        JobLauncherInterface $jobLauncher,
        GetColumnsLinkedToAReferenceEntity $getColumnsLinkedToAReferenceEntity,
        TokenStorageInterface $tokenStorage,
        JobInstanceRepository $jobInstanceRepository,
        JobInstance $jobInstance,
        CreateJobInstanceInterface $createJobInstance
    ) {
        $recordsDeletedEvent = new RecordsDeletedEvent(
            [RecordIdentifier::create('id', 'code', 'fingerprint')],
            [RecordCode::fromString('code')],
            ReferenceEntityIdentifier::fromString('id')
        );

        $getColumnsLinkedToAReferenceEntity->forIdentifier('id')->willReturn(
            [
                ['attribute_code' => 'attribute_code', 'column_code' => 'column_code'],
            ]
        );
        $user = new User();
        $tokenStorage->getToken()->willReturn(new SystemUserToken($user));
        $jobInstanceRepository->findOneByIdentifier('jobName')->shouldBeCalledTimes(2)->willReturn(null, $jobInstance);

        $createJobInstance->createJobInstance(
            [
                'code' => 'jobName',
                'label' => 'Remove the non existing values from product and product models table attribute',
                'job_name' => 'jobName',
                'type' => 'jobName',
            ]
        )->shouldBeCalledOnce();
        $jobLauncher->launch(
            $jobInstance,
            $user,
            [
                'attribute_code' => 'attribute_code',
                'removed_options_per_column_code' => [
                    'column_code' => ['code'],
                ],
            ]
        )->shouldBeCalledOnce();
        $this->whenRecordsAreDeleted($recordsDeletedEvent);
    }
}
