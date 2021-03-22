<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\CLI;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\OptionCollectionData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\OptionData;
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

class RefreshRecordOptionsTest extends SqlIntegrationTestCase
{
    /** @var ReferenceEntityIdentifier */
    private $currentReferenceEntityIdentifier;

    /** @var AttributeIdentifier */
    private $currentAttributeIdentifier;

    /** @var RecordIdentifier */
    private $currentRecordIdentifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_refreshes_a_record_having_an_option_that_has_been_removed(): void
    {
        $this->createOptionAttributeWithOptions(['red']);
        $this->createRecordHavingOption('red');
        $this->removeOptionFromAttribute('red');
        $this->assertTrue($this->IsRecordHavingValue('red'));

        $this->runRefreshRecordsCommand();

        $this->assertFalse($this->IsRecordHavingValue('red'));
    }

    /**
     * @test
     */
    public function it_refreshes_a_record_having_one_of_its_option_removed(): void
    {
        $this->createOptionCollectionAttributeWithOptions(['red', 'blue']);
        $this->createRecordHavingOptions(['red', 'blue']);
        $this->removeOptionFromAttribute('red');
        $this->assertTrue($this->IsRecordHavingValue('red'));
        $this->assertTrue($this->IsRecordHavingValue('blue'));

        $this->runRefreshRecordsCommand();

        $this->assertTrue($this->IsRecordHavingValue('blue'));
        $this->assertFalse($this->IsRecordHavingValue('red'));
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

    /**
     * @param string[] $options
     */
    private function createOptionAttributeWithOptions(array $options)
    {
        $this->loadReferenceEntity('designer');
        $this->loadOptionAttributeWithOptions($options);
    }

    /**
     * @param string[] $options
     */
    private function createOptionCollectionAttributeWithOptions(array $options)
    {
        $this->loadReferenceEntity('designer');
        $this->loadOptionCollectionAttributeWithOptions($options);
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
        $this->currentReferenceEntityIdentifier = $referenceEntityIdentifier;
    }

    private function loadOptionAttributeWithOptions($optionCodes): void
    {
        $this->currentAttributeIdentifier = AttributeIdentifier::fromString('color');
        $optionAttribute = OptionAttribute::create(
            $this->currentAttributeIdentifier,
            $this->currentReferenceEntityIdentifier,
            AttributeCode::fromString('color'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );
        foreach ($optionCodes as $optionCode) {
            $optionAttribute->addOption(AttributeOption::create(OptionCode::fromString($optionCode),
                LabelCollection::fromArray([])));
        }

        /** @var AttributeRepositoryInterface $attributeRepository */
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attributeRepository->create($optionAttribute);
    }

    private function loadOptionCollectionAttributeWithOptions($optionCodes): void
    {
        $this->currentAttributeIdentifier = AttributeIdentifier::fromString('color');
        $optionAttribute = OptionCollectionAttribute::create(
            $this->currentAttributeIdentifier,
            $this->currentReferenceEntityIdentifier,
            AttributeCode::fromString('color'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );
        foreach ($optionCodes as $optionCode) {
            $optionAttribute->addOption(AttributeOption::create(OptionCode::fromString($optionCode),
                LabelCollection::fromArray([])));
        }

        /** @var AttributeRepositoryInterface $attributeRepository */
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attributeRepository->create($optionAttribute);
    }

    private function createRecordHavingOption(string $optionCode): void
    {
        /** @var RecordRepositoryInterface $recordRepository */
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $this->currentRecordIdentifier = RecordIdentifier::fromString('a_record');
        $recordRepository->create(
            Record::create(
                $this->currentRecordIdentifier,
                $this->currentReferenceEntityIdentifier,
                RecordCode::fromString('a_record'),
                ValueCollection::fromValues([
                    Value::create(
                        $this->currentAttributeIdentifier,
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        OptionData::createFromNormalize($optionCode)
                    ),
                ])
            )
        );
    }

    /**
     * @param string[] $optionCodes
     */
    private function createRecordHavingOptions(array $optionCodes): void
    {
        /** @var RecordRepositoryInterface $recordRepository */
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $this->currentRecordIdentifier = RecordIdentifier::fromString('a_record');
        $optionCodes = array_map(
            function (string $optionCode) {
                return OptionCode::fromString($optionCode);
            }, $optionCodes
        );
        $recordRepository->create(
            Record::create(
                $this->currentRecordIdentifier,
                $this->currentReferenceEntityIdentifier,
                RecordCode::fromString('a_record'),
                ValueCollection::fromValues([
                    Value::create(
                        $this->currentAttributeIdentifier,
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        OptionCollectionData::fromOptionCodes($optionCodes)
                    ),
                ])
            )
        );
    }

    private function removeOptionFromAttribute(string $optionToRemove): void
    {
        /** @var AttributeRepositoryInterface $attributeRepository */
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        /** @var OptionAttribute $optionAttribute */
        $optionAttribute = $attributeRepository->getByIdentifier($this->currentAttributeIdentifier);
        $optionsToKeep = array_filter(
            $optionAttribute->getAttributeOptions(),
            function (AttributeOption $option) use ($optionToRemove) {
                return $optionToRemove !== (string) $option->getCode();
            }
        );
        $optionAttribute->setOptions($optionsToKeep);
        $attributeRepository->update($optionAttribute);
    }

    private function IsRecordHavingValue(string $optionCode): bool
    {
        /** @var Connection $sqlConnection */
        $sqlConnection = $this->get('database_connection');
        $statement = $sqlConnection->executeQuery(
            'SELECT value_collection FROM akeneo_reference_entity_record WHERE identifier = :identifier',
            [
                'identifier' => $this->currentRecordIdentifier->normalize(),
            ]
        );
        $result = $statement->fetch(\PDO::FETCH_COLUMN);
        $values = json_decode($result, true);

        if (!isset($values[$this->currentAttributeIdentifier->normalize()])) {
            return false;
        }

        $data = $values[$this->currentAttributeIdentifier->normalize()]['data'];
        if (is_array($data)) {
            return in_array($optionCode, $data);
        }

        return $data === $optionCode;
    }
}
