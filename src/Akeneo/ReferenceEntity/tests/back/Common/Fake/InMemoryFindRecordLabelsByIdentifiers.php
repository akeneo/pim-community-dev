<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKey;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordLabelsByIdentifiersInterface;

class InMemoryFindRecordLabelsByIdentifiers implements FindRecordLabelsByIdentifiersInterface
{
    /** @var InMemoryRecordRepository  */
    private $recordRepository;

    /** @var InMemoryReferenceEntityRepository  */
    private $referenceEntityRepository;

    public function __construct(
        InMemoryRecordRepository $recordRepository,
        InMemoryReferenceEntityRepository $referenceEntityRepository
    ) {
        $this->recordRepository = $recordRepository;
        $this->referenceEntityRepository = $referenceEntityRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function find(array $recordIdentifiers): array
    {
        $recordLabels = [];
        foreach ($recordIdentifiers as $identifier) {
            $recordIdentifier = RecordIdentifier::fromString($identifier);
            $record = $this->recordRepository->getByIdentifier($recordIdentifier);
            $referenceEntity = $this->referenceEntityRepository->getByIdentifier($record->getReferenceEntityIdentifier());

            $valueKey = ValueKey::createFromNormalized($referenceEntity->getAttributeAsLabelReference()->normalize());
            $value = $record->findValue($valueKey);
            $recordLabels[$recordIdentifier->normalize()] = $value->getData()->normalize();
        }

        return $recordLabels;
    }
}
