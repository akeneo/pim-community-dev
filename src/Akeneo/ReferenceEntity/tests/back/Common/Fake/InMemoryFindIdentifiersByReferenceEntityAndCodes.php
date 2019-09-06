<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
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

    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier, array $recordCodes): array
    {
        return array_map(
            function (RecordCode $recordCode) use ($referenceEntityIdentifier) {
                return $this->recordRepository->getByReferenceEntityAndCode($referenceEntityIdentifier, $recordCode);
            },
            $recordCodes
        );
    }
}
