<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Record\IndexRecords;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\RecordIndexerInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IndexRecordsByReferenceEntity
{
    /** @var RecordIndexerInterface */
    private $recordIndexer;

    public function __construct(RecordIndexerInterface $recordIndexer)
    {
        $this->recordIndexer = $recordIndexer;
    }

    public function __invoke(IndexRecordsByReferenceEntityCommand $command) :void
    {
        $refenceEntityIdentifier = ReferenceEntityIdentifier::fromString($command->referenceEntityIdentifier);
        $this->recordIndexer->indexByReferenceEntity($refenceEntityIdentifier);
    }
}
