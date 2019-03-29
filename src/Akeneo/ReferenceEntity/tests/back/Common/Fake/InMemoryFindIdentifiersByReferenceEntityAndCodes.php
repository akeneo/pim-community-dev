<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersByReferenceEntityAndCodesInterface;

class InMemoryFindIdentifiersByReferenceEntityAndCodes implements FindIdentifiersByReferenceEntityAndCodesInterface
{
    /** @var InMemoryRecordRepository */
    private $recordRepository;

    public function __construct(InMemoryRecordRepository $recordRepository)
    {
        $this->recordRepository = $recordRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier, array $recordCodes): array
    {
        $identifiers = [];

        foreach($this->recordRepository->all() as $record) {
            if (
                $record->getReferenceEntityIdentifier()->equals($referenceEntityIdentifier)
                && in_array($record->getCode(), $recordCodes)
            ) {
                $identifiers[$record->getCode()->normalize()] = $record->getIdentifier();
            }
        }

        return $identifiers;
    }
}
