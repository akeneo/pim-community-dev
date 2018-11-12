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
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\FindRecordsForConnectorByIdentifiersInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\RecordForConnector;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

class SqlFindRecordsForConnectorByIdentifiersTest extends SqlIntegrationTestCase
{
    /** @var RecordRepositoryInterface */
    private $repository;

    /** @var FindRecordsForConnectorByIdentifiersInterface */
    private $findRecordsForConnectorQuery;

    /** @var SaverInterface */
    private $fileInfoSaver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $this->findRecordsForConnectorQuery = $this->get('akeneo_referenceentity.infrastructure.persistence.query.find_records_for_connector_by_identifiers');
        $this->fileInfoSaver = $this->get('akeneo_file_storage.saver.file');

        $this->resetDB();
        $this->loadReferenceEntityWithAttributes();
    }

    /**
     * @test
     */
    public function it_finds_records_from_a_list_of_identifiers()
    {
        $this->loadRecords(['starck', 'dyson', 'newson']);
        $this->loadRecords(['unexpected_record']);

        $expectedRecordsForConnector = $this->createRecordsForConnector(['dyson', 'newson', 'starck']);
        $identifiers = ['designer_dyson_fingerprint', 'designer_newson_fingerprint', 'designer_starck_fingerprint'];

        $recordsForConnectorFound = ($this->findRecordsForConnectorQuery)($identifiers);

        $this->assertSameRecordsForConnector($expectedRecordsForConnector, $recordsForConnectorFound);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_no_records_found()
    {
        $this->loadRecords(['starck', 'dyson']);

        $recordsFound = ($this->findRecordsForConnectorQuery)(['foo', 'bar']);
        $this->assertSame([], $recordsFound);
    }

    /**
     * @param RecordForConnector[] $expectedRecordsForConnector
     * @param RecordForConnector[] $recordsForConnectorFound
     */
    private function assertSameRecordsForConnector(array $expectedRecordsForConnector, array $recordsForConnectorFound): void
    {
        $this->assertCount(count($expectedRecordsForConnector), $recordsForConnectorFound);

        foreach ($expectedRecordsForConnector as $index => $recordForConnector) {
            $this->assertSameRecordForConnector($recordForConnector, $recordsForConnectorFound[$index]);
        }
    }

    private function assertSameRecordForConnector(RecordForConnector $expectedRecord, RecordForConnector $currentRecord): void
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
            foreach ($recordValue as $key => $value) {
                if (is_array($value['data'])) {
                    sort($value['data']);
                    $recordValues[$attributeCode][$key] = $value['data'];
                }
            }
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
            $imageInfo = $this->createFileInfo(sprintf('image_%s.jpg', $code));

            $record = Record::create(
                RecordIdentifier::fromString(sprintf('designer_%s_fingerprint', $code)),
                ReferenceEntityIdentifier::fromString('designer'),
                RecordCode::fromString($code),
                ['en_US' => ucfirst($code), 'fr_FR' => ucfirst($code)],
                Image::fromFileInfo($imageInfo),
                ValueCollection::fromValues([
                    Value::create(
                        AttributeIdentifier::fromString('name_designer_fingerprint'),
                        ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                        TextData::fromString(sprintf('Name: %s', $code))
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

    private function createFileInfo(string $fileName): FileInfo
    {
        $fileInfo = (new FileInfo())
            ->setOriginalFilename($fileName)
            ->setKey(sprintf('test/%s', $fileName))
            ->setSize(1024)
            ->setMimeType('image/jpeg')
            ->setExtension('jpg');

        $this->fileInfoSaver->save($fileInfo);

        return $fileInfo;
    }

    /**
     * @param string[] $codes
     *
     * @return RecordForConnector[]
     */
    private function createRecordsForConnector(array $codes): array
    {
        $recordsForConnector = [];
        foreach ($codes as $code) {
            $imageInfo = (new FileInfo())
                ->setOriginalFilename(sprintf('image_%s.jpg', $code))
                ->setKey(sprintf('test/image_%s.jpg', $code));

            $recordsForConnector[] = new RecordForConnector(
                RecordCode::fromString($code),
                LabelCollection::fromArray(['en_US' => ucfirst($code), 'fr_FR' => ucfirst($code)]),
                Image::fromFileInfo($imageInfo),
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

        return $recordsForConnector;
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
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $image = ImageAttribute::create(
            AttributeIdentifier::create('designer', 'image', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('image'),
            LabelCollection::fromArray(['en_US' => 'Image']),
            AttributeOrder::fromInteger(1),
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
