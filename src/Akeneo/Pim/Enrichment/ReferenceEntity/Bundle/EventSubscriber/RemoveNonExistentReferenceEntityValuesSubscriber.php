<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityCollectionType;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\AttributeType\ReferenceEntityType;
use Akeneo\ReferenceEntity\Domain\Event\RecordsDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

class RemoveNonExistentReferenceEntityValuesSubscriber implements EventSubscriberInterface
{
    private const REMOVE_NON_EXISTENT_VALUES_JOB = 'remove_non_existing_product_values';

    /** @var array<string, string> */
    private ?array $referenceEntityAttributes;

    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        private JobLauncherInterface $jobLauncher,
        private Connection $connection
    ) {
        $this->referenceEntityAttributes = null;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RecordsDeletedEvent::class => 'onBulkRecordsDeleted',
        ];
    }

    public function onBulkRecordsDeleted(RecordsDeletedEvent $event): void
    {
        $this->launchJob($event->getReferenceEntityIdentifier()->normalize(), $event->getRecordCodes());
    }

    private function launchJob(string $referenceEntityIdentifier, array $recordCodes): void
    {
        Assert::allIsInstanceOf($recordCodes, RecordCode::class);

        $referenceEntityAttributes = $this->getReferenceEntityAttributesForReferenceEntity($referenceEntityIdentifier);
        if ([] === $referenceEntityAttributes) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $jobInstance = $this->getJobInstance();

        foreach ($referenceEntityAttributes as $attributeCode) {
            $this->jobLauncher->launch(
                $jobInstance,
                $user,
                [
                    'attribute_code' => $attributeCode,
                    'attribute_options' => \array_map(
                        fn (RecordCode $recordCode): string => $recordCode->normalize(),
                        $recordCodes
                    ),
                ]
            );
        }
    }

    private function getJobInstance(): JobInstance
    {
        return $this->jobInstanceRepository->findOneByIdentifier(self::REMOVE_NON_EXISTENT_VALUES_JOB);
    }

    private function getReferenceEntityAttributesForReferenceEntity(string $referenceEntityIdentifier): array
    {
        if (null === $this->referenceEntityAttributes) {
            $this->referenceEntityAttributes = [];

            $rows = $this->connection->executeQuery(
                'SELECT code, properties FROM pim_catalog_attribute WHERE attribute_type IN (:types)',
                [
                    'types' => [
                        ReferenceEntityType::REFERENCE_ENTITY,
                        ReferenceEntityCollectionType::REFERENCE_ENTITY_COLLECTION
                    ],
                ],
                ['types' => Connection::PARAM_STR_ARRAY]
            )->fetchAllAssociative();

            foreach ($rows as $row) {
                $properties = \unserialize($row['properties']);
                $referenceDataName = $properties['reference_data_name'] ?? null;
                if (null !== $referenceDataName) {
                    $this->referenceEntityAttributes[$referenceDataName][] = $row['code'];
                }
            }
        }

        return $this->referenceEntityAttributes[$referenceEntityIdentifier] ?? [];
    }
}
