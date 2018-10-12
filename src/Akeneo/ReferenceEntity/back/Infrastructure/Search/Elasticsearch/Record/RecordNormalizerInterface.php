<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;

interface RecordNormalizerInterface
{
    public function normalize(Record $record): array;
}
