<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordLabelsByCodesInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class InMemoryFindRecordLabelsByCodes implements FindRecordLabelsByCodesInterface
{
    /** @var InMemoryRecordRepository */
    private $recordRepository;

    /** @var InMemoryFindReferenceEntityAttributeAsLabel */
    private $findReferenceEntityAttributeAsLabel;

    public function __construct(
        InMemoryRecordRepository $recordRepository,
        InMemoryFindReferenceEntityAttributeAsLabel $findReferenceEntityAttributeAsLabel
    ) {
        $this->recordRepository = $recordRepository;
        $this->findReferenceEntityAttributeAsLabel = $findReferenceEntityAttributeAsLabel;
    }

    /**
     * {@inheritdoc}
     */
    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier, array $recordCodes): array
    {
        $attributeAsLabel = ($this->findReferenceEntityAttributeAsLabel)($referenceEntityIdentifier)->normalize();
        $records = $this->recordRepository->getByReferenceEntityAndCodes($referenceEntityIdentifier, $recordCodes);

        $labelCollectionPerRecord = [];
        /** @var Record $record */
        foreach ($records as $record) {
            $values = $record->getValues()->normalize();
            $recordCode = $record->getCode()->normalize();

            $labelsIndexedPerLocale = [];
            foreach ($values as $value) {
                if ($value['attribute'] === $attributeAsLabel) {
                    $labelsIndexedPerLocale[$value['locale']] = $value['data'];
                }
            }

            $labelCollectionPerRecord[$recordCode] = LabelCollection::fromArray($labelsIndexedPerLocale);
        }

        return $labelCollectionPerRecord;
    }
}
