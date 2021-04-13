<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
interface RecordQueryBuilderInterface
{
    public function buildFromQuery(RecordQuery $recordQuery, $source): array;
}
