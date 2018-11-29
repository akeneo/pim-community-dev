<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Persistence\InMemory;

use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindRecordItemsForIdentifiersWithRecordQuery;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryRecordRepository;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordItem;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryFindRecordItemsForIdentifiersTest extends TestCase
{
    /** @var InMemoryRecordRepository */
    private $recordRepository;

    /** @var RecordIdentifier */
    private $starckIdentifier;

    /** @var RecordIdentifier */
    private $cocoIdentifier;

    /** @var InMemoryFindRecordItemsForIdentifiersWithRecordQuery */
    private $query;

    public function setup()
    {
        $this->recordRepository = new InMemoryRecordRepository();
        $this->query = new InMemoryFindRecordItemsForIdentifiersWithRecordQuery($this->recordRepository);
    }

    /**
     * @test
     */
    public function it_return_an_empty_list_of_records_if_identifiers_are_not_matching()
    {
        $result = ($this->query)(['brand_kartell_fingerprint', 'brand_dyson_fingerprint']);

        Assert::assertSame([], $result);
    }

    /**
     * @test
     */
    public function it_return_a_list_of_record_items_for_the_given_identifiers()
    {
        $this->loadRecords();

        $result = ($this->query)([$this->starckIdentifier, $this->cocoIdentifier]);
        $starkRecordItem = new RecordItem();
        $starkRecordItem->identifier = (string) $this->starckIdentifier;
        $starkRecordItem->referenceEntityIdentifier = 'designer';
        $starkRecordItem->code = 'starck';
        $starkRecordItem->labels = [
            'fr_FR' => 'Philippe Starck'
        ];
        $starkRecordItem->image = null;
        $starkRecordItem->values = [];

        $cocoRecordItem = new RecordItem();
        $cocoRecordItem->identifier = (string) $this->cocoIdentifier;
        $cocoRecordItem->referenceEntityIdentifier = 'designer';
        $cocoRecordItem->code = 'coco';
        $cocoRecordItem->labels = [
            'fr_FR' => 'Coco Chanel'
        ];
        $cocoRecordItem->image = null;
        $cocoRecordItem->values = [];

        $normalizedResults = array_map(function (RecordItem $item) {
            return $item->normalize();
        }, $result);

        Assert::assertSame(
            [$starkRecordItem->normalize(), $cocoRecordItem->normalize()],
            $normalizedResults
        );
    }

    /**
     * @test
     */
    public function it_return_a_partial_list_of_record_items_for_the_given_identifiers()
    {
        $this->loadRecords();

        $result = ($this->query)(['michel_sardou', $this->cocoIdentifier]);
        $starkRecordItem = new RecordItem();
        $starkRecordItem->identifier = (string) $this->starckIdentifier;
        $starkRecordItem->referenceEntityIdentifier = 'designer';
        $starkRecordItem->code = 'starck';
        $starkRecordItem->labels = [
            'fr_FR' => 'Philippe Starck'
        ];
        $starkRecordItem->image = null;
        $starkRecordItem->values = [];

        $cocoRecordItem = new RecordItem();
        $cocoRecordItem->identifier = (string) $this->cocoIdentifier;
        $cocoRecordItem->referenceEntityIdentifier = 'designer';
        $cocoRecordItem->code = 'coco';
        $cocoRecordItem->labels = [
            'fr_FR' => 'Coco Chanel'
        ];
        $cocoRecordItem->image = null;
        $cocoRecordItem->values = [];

        $normalizedResults = array_map(function (RecordItem $item) {
            return $item->normalize();
        }, $result);

        Assert::assertSame(
            [$cocoRecordItem->normalize()],
            $normalizedResults
        );
    }

    private function loadRecords(): void
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');

        $starkCode = RecordCode::fromString('starck');
        $this->starckIdentifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $starkCode);
        $this->recordRepository->create(
            Record::create(
                $this->starckIdentifier,
                $referenceEntityIdentifier,
                $starkCode,
                ['fr_FR' => 'Philippe Starck'],
                Image::createEmpty(),
                ValueCollection::fromValues([])
            )
        );
        $cocoCode = RecordCode::fromString('coco');
        $this->cocoIdentifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $cocoCode);
        $this->recordRepository->create(
            Record::create(
                $this->cocoIdentifier,
                $referenceEntityIdentifier,
                $cocoCode,
                ['fr_FR' => 'Coco Chanel'],
                Image::createEmpty(),
                ValueCollection::fromValues([])
            )
        );
    }
}
