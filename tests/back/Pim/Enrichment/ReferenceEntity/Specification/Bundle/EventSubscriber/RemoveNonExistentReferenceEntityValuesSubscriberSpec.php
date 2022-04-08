<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\ReferenceEntity\Domain\Event\RecordsDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RemoveNonExistentReferenceEntityValuesSubscriberSpec extends ObjectBehavior
{
    function let(
        TokenStorageInterface $tokenStorage,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        Connection $connection,
        JobInstance $jobInstance,
        Result $statement,
        TokenInterface $token,
        UserInterface $user,
    )
    {
        $jobInstanceRepository->findOneByIdentifier('remove_non_existing_product_values')->willReturn($jobInstance);
        $connection->executeQuery(
            'SELECT code, properties FROM pim_catalog_attribute WHERE attribute_type IN (:types)',
            [
                'types' => [
                    ReferenceEntityType::REFERENCE_ENTITY,
                    ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION
                ],
            ],
            ['types' => Connection::PARAM_STR_ARRAY]
        )->willReturn($statement);
        $statement->fetchAllAssociative()->willReturn([
            [ 'code' => 'attribute_1', 'properties' => \serialize(['reference_data_name' => 'color'])],
            [ 'code' => 'attribute_2', 'properties' => \serialize(['reference_data_name' => 'color'])]
        ]);

        $tokenStorage->getToken()->WillReturn($token);
        $token->getUser()->willReturn($user);

        $this->beConstructedWith($tokenStorage, $jobInstanceRepository, $jobLauncher, $connection);
    }

    function it_should_launch_a_job_per_attribute_on_records_deleted(
        JobLauncherInterface $jobLauncher,
        JobInstance $jobInstance,
        UserInterface $user
    )
    {
        $event = new RecordsDeletedEvent(
            [
                RecordIdentifier::fromString('black_identifier'),
                RecordIdentifier::fromString('white_identifier'),
            ],
            [
                RecordCode::fromString('black'),
                RecordCode::fromString('white'),
            ],
            ReferenceEntityIdentifier::fromString('color')
        );

        $jobLauncher->launch(
            $jobInstance,
            $user,
            [
                'attribute_code' => 'attribute_1',
                'attribute_options' => ['black', 'white'],
            ]
        )->shouldBeCalledOnce();

        $jobLauncher->launch(
            $jobInstance,
            $user,
            [
                'attribute_code' => 'attribute_2',
                'attribute_options' => ['black', 'white'],
            ]
        )->shouldBeCalledOnce();

        $this->onBulkRecordsDeleted($event);
    }
}
