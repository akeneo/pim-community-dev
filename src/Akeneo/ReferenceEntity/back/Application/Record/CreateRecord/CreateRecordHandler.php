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

namespace Akeneo\ReferenceEntity\Application\Record\CreateRecord;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityAttributeAsLabelInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class CreateRecordHandler
{
    /** @var RecordRepositoryInterface */
    private $recordRepository;

    /** @var FindReferenceEntityAttributeAsLabelInterface */
    private $findAttributeAsLabel;

    public function __construct(
        RecordRepositoryInterface $recordRepository,
        FindReferenceEntityAttributeAsLabelInterface $findAttributeAsLabel
    ) {
        $this->recordRepository = $recordRepository;
        $this->findAttributeAsLabel = $findAttributeAsLabel;
    }

    public function __invoke(CreateRecordCommand $createRecordCommand): void
    {
        $code = RecordCode::fromString($createRecordCommand->code);
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($createRecordCommand->referenceEntityIdentifier);
        $identifier = $this->recordRepository->nextIdentifier($referenceEntityIdentifier, $code);
        $labelValues = $this->getLabelValues($createRecordCommand, $referenceEntityIdentifier);

        $record = Record::create(
            $identifier,
            $referenceEntityIdentifier,
            $code,
            ValueCollection::fromValues($labelValues)
        );

        $this->recordRepository->create($record);
    }

    private function getLabelValues(CreateRecordCommand $createRecordCommand, ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        if (empty($createRecordCommand->labels)) {
            return [];
        }

        /** @var AttributeAsLabelReference $attributeAsLabelReference */
        $attributeAsLabelReference = ($this->findAttributeAsLabel)($referenceEntityIdentifier);
        if ($attributeAsLabelReference->isEmpty()) {
            return [];
        }

        $labelValues = [];
        foreach ($createRecordCommand->labels as $locale => $label) {
            $labelValues[] = Value::create(
                $attributeAsLabelReference->getIdentifier(),
                ChannelReference::noReference(),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode($locale)),
                TextData::createFromNormalize($label)
            );
        }

        return $labelValues;
    }
}
