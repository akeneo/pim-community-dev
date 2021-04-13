<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\Record;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\ConnectorRecord;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\FindConnectorRecordsByIdentifiersInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

class SqlFindConnectorRecordsByIdentifiersTest extends SqlIntegrationTestCase
{
    /** @var RecordRepositoryInterface */
    private $repository;

    /** @var FindConnectorRecordsByIdentifiersInterface */
    private $findConnectorRecordsQuery;

    /** @var SaverInterface */
    private $fileInfoSaver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $this->findConnectorRecordsQuery = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_connector_records_by_identifiers');
        $this->fileInfoSaver = $this->get('akeneo_file_storage.saver.file');

        $this->resetDB();
        $this->loadReferenceEntityWithAttributes();
    }

    /**
     * @test
     */
    public function it_finds_records_from_a_list_of_identifiers()
    {
        $this->loadRecords(['Starck_with_uppercase', 'starck', 'dyson', 'newson']);
        $this->loadRecords(['unexpected_record']);

        $recordQuery = RecordQuery::createPaginatedQueryUsingSearchAfter(
            ReferenceEntityIdentifier::fromString('designer'),
            ChannelReference::noReference(),
            LocaleIdentifierCollection::empty(),
            100,
            null,
            []
        );
        $identifiers = ['designer_Starck_with_uppercase_fingerprint ', 'designer_dyson_fingerprint', 'designer_newson_fingerprint', 'designer_starck_fingerprint'];

        $expectedConnectorRecords = [];
        foreach (['Starck_with_uppercase', 'dyson', 'newson', 'starck'] as $code) {
            $expectedConnectorRecords[] = new ConnectorRecord(
                RecordCode::fromString($code),
                [
                    'name'  => [
                        [
                            'locale'  => 'en_US',
                            'channel' => 'ecommerce',
                            'data'    => sprintf('Name: %s', $code),
                        ],
                        [
                            'locale'  => 'en_US',
                            'channel' => 'print',
                            'data'    => sprintf('Name: %s for print channel', $code),
                        ],
                        [
                            'locale'  => 'fr_FR',
                            'channel' => 'ecommerce',
                            'data'    => sprintf('Nom: %s', $code),
                        ]
                    ]
                ]
            );
        }

        $connectorRecordsFound = $this->findConnectorRecordsQuery->find($identifiers, $recordQuery);

        $this->assertSameConnectorRecords($expectedConnectorRecords, $connectorRecordsFound);
    }

    /**
     * @test
     */
    public function it_finds_records_from_a_list_of_identifiers_with_values_filtered_by_channel()
    {
        $this->loadRecords(['starck', 'dyson', 'newson']);
        $this->loadRecords(['unexpected_record']);

        $recordQuery = RecordQuery::createPaginatedQueryUsingSearchAfter(
            ReferenceEntityIdentifier::fromString('designer'),
            ChannelReference::createFromNormalized('ecommerce'),
            LocaleIdentifierCollection::empty(),
            100,
            null,
            []
        );
        $identifiers = ['designer_dyson_fingerprint', 'designer_newson_fingerprint', 'designer_starck_fingerprint'];

        $expectedConnectorRecords = [];
        foreach (['dyson', 'newson', 'starck'] as $code) {
            $expectedConnectorRecords[] = new ConnectorRecord(
                RecordCode::fromString($code),
                [
                    'name'  => [
                        [
                            'locale'  => 'en_US',
                            'channel' => 'ecommerce',
                            'data'    => sprintf('Name: %s', $code),
                        ],
                        [
                            'locale'  => 'fr_FR',
                            'channel' => 'ecommerce',
                            'data'    => sprintf('Nom: %s', $code),
                        ]
                    ]
                ]
            );
        }

        $connectorRecordsFound = $this->findConnectorRecordsQuery->find($identifiers, $recordQuery);

        $this->assertSameConnectorRecords($expectedConnectorRecords, $connectorRecordsFound);
    }

    /**
     * @test
     */
    public function it_finds_records_from_a_list_of_identifiers_with_values_filtered_by_locales()
    {
        $this->loadRecords(['starck', 'dyson', 'newson']);
        $this->loadRecords(['unexpected_record']);

        $recordQuery = RecordQuery::createPaginatedQueryUsingSearchAfter(
            ReferenceEntityIdentifier::fromString('designer'),
            ChannelReference::createFromNormalized('ecommerce'),
            LocaleIdentifierCollection::fromNormalized(['fr_FR']),
            100,
            null,
            []
        );
        $identifiers = ['designer_dyson_fingerprint', 'designer_newson_fingerprint', 'designer_starck_fingerprint'];

        $expectedConnectorRecords = [];
        foreach (['dyson', 'newson', 'starck'] as $code) {
            $expectedConnectorRecords[] = new ConnectorRecord(
                RecordCode::fromString($code),
                [
                    'name'  => [
                        [
                            'locale'  => 'fr_FR',
                            'channel' => 'ecommerce',
                            'data'    => sprintf('Nom: %s', $code),
                        ]
                    ]
                ]
            );
        }

        $connectorRecordsFound = $this->findConnectorRecordsQuery->find($identifiers, $recordQuery);

        $this->assertSameConnectorRecords($expectedConnectorRecords, $connectorRecordsFound);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_no_records_found()
    {
        $this->loadRecords(['starck', 'dyson']);

        $recordQuery = RecordQuery::createPaginatedQueryUsingSearchAfter(
            ReferenceEntityIdentifier::fromString('designer'),
            ChannelReference::noReference(),
            LocaleIdentifierCollection::empty(),
            100,
            null,
            []
        );

        $recordsFound = $this->findConnectorRecordsQuery->find(['foo', 'bar'], $recordQuery);
        $this->assertSame([], $recordsFound);
    }

    /**
     * @param ConnectorRecord[] $expectedConnectorRecords
     * @param ConnectorRecord[] $connectorRecordsFound
     */
    private function assertSameConnectorRecords(array $expectedConnectorRecords, array $connectorRecordsFound): void
    {
        $this->assertCount(count($expectedConnectorRecords), $connectorRecordsFound);

        foreach ($expectedConnectorRecords as $index => $connectorRecord) {
            $this->assertSameConnectorRecord($connectorRecord, $connectorRecordsFound[$index]);
        }
    }

    private function assertSameConnectorRecord(ConnectorRecord $expectedRecord, ConnectorRecord $currentRecord): void
    {
        $expectedRecord = $expectedRecord->normalize();
        $expectedRecord['values'] = $this->sortRecordValues($expectedRecord['values']);

        $currentRecord = $currentRecord->normalize();
        $currentRecord['values'] = $this->sortRecordValues($currentRecord['values']);

        $this->assertSame($expectedRecord, $currentRecord);
    }

    private function sortRecordValues(array $recordValues): array
    {
        ksort($recordValues);

        foreach ($recordValues as $attributeCode => $recordValue) {
            usort($recordValue, function ($firstValue, $secondValue) {
                $firstData = is_array($firstValue['data']) ? implode(',', sort($firstValue['data'])) : $firstValue['data'];
                $secondData = is_array($secondValue['data']) ? implode(',', sort($secondValue['data'])) : $secondValue['data'];

                return strcasecmp($firstData, $secondData);
            });

            $recordValues[$attributeCode] = $recordValue;
        }

        return $recordValues;
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @param string[] $codes
     */
    private function loadRecords(array $codes): void
    {
        foreach ($codes as $code) {
            $record = Record::create(
                RecordIdentifier::fromString(sprintf('designer_%s_fingerprint', $code)),
                ReferenceEntityIdentifier::fromString('designer'),
                RecordCode::fromString($code),
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('name_designer_fingerprint'),
                        ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString(sprintf('Name: %s', $code))
                    ),
                    Value::create(
                        AttributeIdentifier::fromString('name_designer_fingerprint'),
                        ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('print')),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString(sprintf('Name: %s for print channel', $code))
                    ),
                    Value::create(
                        AttributeIdentifier::fromString('name_designer_fingerprint'),
                        ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                        TextData::fromString(sprintf('Nom: %s', $code))
                    )
                ])
            );

            $records[] = $record;
            $this->repository->create($record);
        }
    }

    private function loadReferenceEntityWithAttributes(): void
    {
        $repository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('designer'),
            [],
            Image::createEmpty()
        );
        $repository->create($referenceEntity);

        $name = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['en_US' => 'Name']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $image = ImageAttribute::create(
            AttributeIdentifier::create('designer', 'main_image', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('main_image'),
            LabelCollection::fromArray(['en_US' => 'Image']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxFileSize::fromString('250.2'),
            AttributeAllowedExtensions::fromList(['png'])
        );

        $attributesRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attributesRepository->create($name);
        $attributesRepository->create($image);
    }
}
