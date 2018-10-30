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

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersForQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\IdentifiersForQueryResult;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;

/**
 * @author    Julien Sanchez <julienakeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindRecordIdentifiersForQuery implements FindIdentifiersForQueryInterface
{
    /** @var Record[] */
    private $records = [];

    public function add(Record $record): void
    {
        $this->records[] = $record;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(RecordQuery $query): IdentifiersForQueryResult
    {
        $referenceEntityFilter = $query->getFilter('reference_entity');
        $fullTextFilter = ($query->hasFilter('full_text')) ? $query->getFilter('full_text') : null;
        $codeFilter = ($query->hasFilter('code')) ? $query->getFilter('code') : null;

        $records = array_values(array_filter($this->records, function (Record $record) use ($referenceEntityFilter) {
            return '' === $referenceEntityFilter['value']
                || (string) $record->getReferenceEntityIdentifier() === $referenceEntityFilter['value'];
        }));

        $records = array_values(array_filter($records, function (Record $record) use ($fullTextFilter, $query) {
            return null === $fullTextFilter
                || '' === $fullTextFilter['value']
                || false !== strpos((string) $record->getCode(), $fullTextFilter['value'])
                || false !== strpos($record->getLabel($query->getLocale()), $fullTextFilter['value']);
        }));

        $records = array_values(array_filter($records, function (Record $record) use ($codeFilter): bool {
            if (null === $codeFilter) {
                return true;
            }

            $codes = explode(',', $codeFilter['value']);

            if ('NOT IN' === $codeFilter['operator']) {
                return !in_array($record->getCode(), $codes);
            }

            if ('IN' === $codeFilter['operator']) {
                return in_array($record->getCode(), $codes);
            }

            throw new \LogicException(
                sprintf('Unknown operator %s for code filter', $codeFilter['operator'])
            );
        }));

        $result = new IdentifiersForQueryResult();
        $result->total = count($records);
        $result->identifiers = array_map(function (Record $record): string {
            return (string) $record->getIdentifier();
        }, $records);

        return $result;
    }
}
