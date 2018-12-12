<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\FindLinkedRecords;
use Akeneo\ReferenceEntity\Integration\SearchIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class FindLinkedRecordsTest extends SearchIntegrationTestCase
{
    /** @var FindLinkedRecords */
    private $findLinkedRecords;

    public function setUp()
    {
        parent::setUp();

        $this->findLinkedRecords = $this->get('akeneo_referenceentity.infrastructure.search.elasticsearch.record.query.find_linked_records');
        $this->resetDB();
        $this->loadDataset();
    }

    /**
     * @test
     */
    public function it_finds_records_linked_to_other_records(): void
    {
        $linkedRecords = ($this->findLinkedRecords)(RecordIdentifier::create('brand', 'kartell', 'fingerprint'));
        $this->assertSameRecordIdentifiers(['stark_designer'], $linkedRecords);
    }

    private function loadDataset(): void
    {
        $repository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $brandIdentifier = ReferenceEntityIdentifier::fromString('brand');
        $designerIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $repository->create(ReferenceEntity::create($brandIdentifier, [], Image::createEmpty()));
        $repository->create(ReferenceEntity::create($designerIdentifier, [], Image::createEmpty()));

        $workForBrand = RecordAttribute::create(
            AttributeIdentifier::create('designer', 'work_for_brand', 'fingerprint'),
            $designerIdentifier,
            AttributeCode::fromString('work_for_brand'),
            LabelCollection::fromArray(['en_US' => 'Work for brand']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            $brandIdentifier
        );
        $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute')->create($workForBrand);

        $kartell = Record::create(
            RecordIdentifier::create('brand', 'kartell', 'fingerprint'),
            $brandIdentifier,
            RecordCode::fromString('kartell'),
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([])
        );
        $starkLinkedToKartell = Record::create(
            RecordIdentifier::create('designer', 'stark', 'fingerprint'),
            $designerIdentifier,
            RecordCode::fromString('stark'),
            [],
            Image::createEmpty(),
            ValueCollection::fromValues([
               Value::create(
                   $workForBrand->getIdentifier(),
                   ChannelReference::noReference(),
                   LocaleReference::noReference(),
                   RecordData::fromRecordCode($kartell->getCode())
               )
            ])
        );
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $recordRepository->create($kartell);
        $recordRepository->create($starkLinkedToKartell);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @param string[] $expectedIdentifiers
     * @param RecordIdentifier[] $actualIdentifiers
     */
    protected function assertSameRecordIdentifiers(array $expectedIdentifiers, array $actualIdentifiers): void
    {
        $actualIdentifiers = array_map(function (RecordIdentifier $recordIdentifier) {
            return $recordIdentifier->normalize();
        }, $actualIdentifiers);
        sort($actualIdentifiers);
        Assert::assertSame($expectedIdentifiers, $actualIdentifiers);
    }
}
