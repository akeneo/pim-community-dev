<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\CLI;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordCollectionData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class RefreshRecordLinksTest extends SqlIntegrationTestCase
{
    /** @var AttributeIdentifier */
    private $currentAttributeIdentifier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_refreshes_a_record_having_a_link_to_a_record_that_has_been_removed(): void
    {
        $this->loadRecordsForReferenceEntity('brand', ['kartell']);
        $this->loadRecordsForReferenceEntity('designer', ['stark']);
        sleep(1);
        $this->createAttributeRecordSingleLinkOnReferenceEntity('brand', 'designer');
        $this->linkRecordFromTo('kartell', 'stark');
        $this->removeRecord('designer', 'stark');
        $this->assertTrue($this->IsRecordHavingValue('kartell', 'stark'));

        $this->runRefreshRecordsCommand();
        $this->assertFalse($this->IsRecordHavingValue('kartell', 'stark'));
    }

    /**
     * @test
     */
    public function it_refreshes_a_record_having_a_one_link_to_a_record_that_has_been_removed(): void
    {
        $this->loadRecordsForReferenceEntity('brand', ['kartell']);
        $this->loadRecordsForReferenceEntity('designer', ['stark', 'dyson']);
        sleep(1);
        $this->createAttributeRecordMultipleLinkOnReferenceEntity('brand', 'designer');
        $this->linkMultipleRecordsFromTo('kartell', ['stark', 'dyson']);
        $this->removeRecord('designer', 'stark');
        $this->assertTrue($this->IsRecordHavingValue('kartell', 'stark'));
        $this->assertTrue($this->IsRecordHavingValue('kartell', 'dyson'));

        $this->runRefreshRecordsCommand();

        $this->assertFalse($this->IsRecordHavingValue('kartell', 'stark'));
        $this->assertTrue($this->IsRecordHavingValue('kartell', 'dyson'));
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function runRefreshRecordsCommand(): void
    {
        $application = new Application(self::$kernel);
        $command = $application->find('akeneo:reference-entity:refresh-records');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);
    }

    private function loadReferenceEntity(string $referenceEntityIdentifier)
    {
        /** @var ReferenceEntityRepositoryInterface $referenceEntityRepository */
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        $referenceEntityRepository->create(
            ReferenceEntity::create(
                $referenceEntityIdentifier,
                [],
                Image::createEmpty()
            )
        );
    }

    private function loadRecordsForReferenceEntity(string $referenceEntityIdentifier, array $recordCodes): void
    {
        $this->loadReferenceEntity($referenceEntityIdentifier);
        foreach ($recordCodes as $recordCode) {
            /** @var RecordRepositoryInterface $recordRepository */
            $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
            $recordRepository->create(
                Record::create(
                    RecordIdentifier::fromString($recordCode),
                    ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
                    RecordCode::fromString($recordCode),
                    ValueCollection::fromValues([])
                )
            );
        }
    }

    private function createAttributeRecordSingleLinkOnReferenceEntity(
        string $fromReferenceEntityIdentifier,
        string $toReferenceEntity
    ): void {
        $this->currentAttributeIdentifier = AttributeIdentifier::fromString('favorite_designer');
        $optionAttribute = RecordAttribute::create(
            $this->currentAttributeIdentifier,
            ReferenceEntityIdentifier::fromString($fromReferenceEntityIdentifier),
            AttributeCode::fromString('favorite_designer'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            ReferenceEntityIdentifier::fromString($toReferenceEntity)
        );

        /** @var AttributeRepositoryInterface $attributeRepository */
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attributeRepository->create($optionAttribute);
    }

    private function createAttributeRecordMultipleLinkOnReferenceEntity(
        string $fromReferenceEntityIdentifier,
        string $toReferenceEntity
    ): void {
        $this->currentAttributeIdentifier = AttributeIdentifier::fromString('favorite_designer');
        $optionAttribute = RecordCollectionAttribute::create(
            $this->currentAttributeIdentifier,
            ReferenceEntityIdentifier::fromString($fromReferenceEntityIdentifier),
            AttributeCode::fromString('favorite_designer'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            ReferenceEntityIdentifier::fromString($toReferenceEntity)
        );

        /** @var AttributeRepositoryInterface $attributeRepository */
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attributeRepository->create($optionAttribute);
    }

    private function linkRecordFromTo(string $fromRecord, string $toRecord): void
    {
        /** @var RecordRepositoryInterface $recordRepository */
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $fromRecord = $recordRepository->getByIdentifier(RecordIdentifier::fromString($fromRecord));
        $fromRecord->setValue(
            Value::create(
                $this->currentAttributeIdentifier,
                ChannelReference::noReference(),
                LocaleReference::noReference(),
                RecordData::createFromNormalize($toRecord)
            )
        );
        $recordRepository->update($fromRecord);
    }

    private function linkMultipleRecordsFromTo(string $fromRecord, array $toRecords): void
    {
        /** @var RecordRepositoryInterface $recordRepository */
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $fromRecord = $recordRepository->getByIdentifier(RecordIdentifier::fromString($fromRecord));
        $fromRecord->setValue(
            Value::create(
                $this->currentAttributeIdentifier,
                ChannelReference::noReference(),
                LocaleReference::noReference(),
                RecordCollectionData::createFromNormalize($toRecords)
            )
        );
        $recordRepository->update($fromRecord);
    }

    private function removeRecord(string $referenceEntityIdentifier, string $recordToRemove): void
    {
        /** @var RecordRepositoryInterface $recordRepository */
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $recordRepository->deleteByReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            RecordCode::fromString($recordToRemove)
        );
    }

    private function IsRecordHavingValue(string $recordFrom, string $recordTo): bool
    {
        /** @var Connection $sqlConnection */
        $sqlConnection = $this->get('database_connection');
        $statement = $sqlConnection->executeQuery(
            'SELECT value_collection FROM akeneo_reference_entity_record WHERE identifier = :identifier',
            [
                'identifier' => $recordFrom,
            ]
        );
        $result = $statement->fetch(\PDO::FETCH_COLUMN);
        $values = json_decode($result, true);

        if (!isset($values[$this->currentAttributeIdentifier->normalize()])) {
            return false;
        }

        $data = $values[$this->currentAttributeIdentifier->normalize()]['data'];
        if (is_array($data)) {
            return in_array($recordTo, $data);
        }

        return $data === $recordTo;
    }
}
