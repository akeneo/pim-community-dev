<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\Analytics;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query\AverageMaxQuery;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
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
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use Ramsey\Uuid\Uuid;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SqlAverageMaxNumberOfValuesPerRecordTest extends SqlIntegrationTestCase
{
    /** @var AverageMaxQuery */
    private $averageMaxNumberOfValuesPerRecords;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var RecordRepositoryInterface */
    protected $recordRepository;

    public function setUp()
    {
        parent::setUp();

        $this->averageMaxNumberOfValuesPerRecords = $this->get('akeneo_referenceentity.infrastructure.persistence.query.analytics.average_max_number_of_values_per_record');
        $this->attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $this->recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_returns_the_average_and_max_number_of_values_per_record()
    {
        $this->loadRecordWithNumberOfValues(2);
        $this->loadRecordWithNumberOfValues(4);

        $volume = $this->averageMaxNumberOfValuesPerRecords->fetch();

        $this->assertEquals('4', $volume->getMaxVolume());
        $this->assertEquals('3', $volume->getAverageVolume());
        $this->assertEquals('average_max_number_of_values_per_record', $volume->getVolumeName());
        $this->assertFalse($volume->hasWarning(), 'There shouldn\'t be a warning for this reference entity volume');
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadRecordWithNumberOfValues(int $numberOfValuesForRecord): void
    {
        $referenceEntityIdentifier = $this->createReferenceEntity();
        $attributes = $this->createAttributes($numberOfValuesForRecord, $referenceEntityIdentifier);

        $this->createRecordWithOneValueForEachAttribute($referenceEntityIdentifier, $attributes);
    }


    private function createReferenceEntity(): ReferenceEntityIdentifier
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($this->randomString());
        $referenceEntityRepository->create(ReferenceEntity::create(
            $referenceEntityIdentifier,
            [],
            Image::createEmpty()
        ));

        return $referenceEntityIdentifier;
    }

    /**
     * @param AttributeInterface[] $attributes
     */
    private function createRecordWithOneValueForEachAttribute(ReferenceEntityIdentifier $referenceEntityIdentifier, array $attributes): void
    {
        $valueCollection = $this->generateValues($attributes);
        $this->recordRepository->create(
            Record::create(
                RecordIdentifier::fromString($this->randomString()),
                $referenceEntityIdentifier,
                RecordCode::fromString($this->randomString()),
                $valueCollection
            )
        );
    }

    /**
     * @param AttributeInterface[] $attributes
     */
    private function generateValues(array $attributes): ValueCollection
    {
        $valueCollection = ValueCollection::fromValues(
            array_map(function (AbstractAttribute $attribute) {
                return Value::create(
                    $attribute->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    TextData::fromString('Some text data')
                );
            }, $attributes)
        );

        return $valueCollection;
    }

    /**
     * @return mixed
     *
     */
    private function randomString(): string
    {
        return str_replace('-', '_', Uuid::uuid4()->toString());
    }

    /**
     * @param int $numberOfValuesForRecord
     * @param     $referenceEntityIdentifier
     *
     * @return array
     *
     */
    private function createAttributes(int $numberOfValuesForRecord, $referenceEntityIdentifier): array
    {
        $attributes = array_map(
            function (int $index) use ($referenceEntityIdentifier) {
                $identifier = sprintf('%s%d', $referenceEntityIdentifier->normalize(), $index);
                $attribute = TextAttribute::createText(
                    AttributeIdentifier::fromString($identifier),
                    $referenceEntityIdentifier,
                    AttributeCode::fromString($identifier),
                    LabelCollection::fromArray([]),
                    AttributeOrder::fromInteger($index + 2), // Labels and Image are created by default
                    AttributeIsRequired::fromBoolean(false),
                    AttributeValuePerChannel::fromBoolean(false),
                    AttributeValuePerLocale::fromBoolean(false),
                    AttributeMaxLength::fromInteger(255),
                    AttributeValidationRule::none(),
                    AttributeRegularExpression::createEmpty()
                );
                $this->attributeRepository->create($attribute);

                return $attribute;
            },
            range(1, $numberOfValuesForRecord)
        );

        return $attributes;
}
}
