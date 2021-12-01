<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Record\IndexRecords;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordIndexerInterface;

/**
 * Indexes all the records of a given reference entity
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexRecordsByReferenceEntityHandler
{
    public function __construct(private RecordIndexerInterface $recordIndexer)
    {
    }

    public function __invoke(IndexRecordsByReferenceEntityCommand $command) :void
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($command->referenceEntityIdentifier);
        $this->recordIndexer->indexByReferenceEntity($referenceEntityIdentifier);
    }
}
