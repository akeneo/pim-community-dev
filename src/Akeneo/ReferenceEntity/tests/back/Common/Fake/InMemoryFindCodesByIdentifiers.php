<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindCodesByIdentifiersInterface;

class InMemoryFindCodesByIdentifiers implements FindCodesByIdentifiersInterface
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
    public function find(array $identifiers): array
    {
        $indexedCodes = [];

        /** @var Record $record */
        foreach ($this->recordRepository->all() as $record) {
            $recordIdentifier = $record->getIdentifier()->normalize();
            $recordCode = $record->getCode()->normalize();

            if (in_array($recordIdentifier, $identifiers)) {
                $indexedCodes[$recordIdentifier] = $recordCode;
            }
        }

        return $indexedCodes;
    }
}
