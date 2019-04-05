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

use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKey;
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

    /** @var InMemoryReferenceEntityRepository  */
    private $referenceEntityRepository;

    /** @var InMemoryFindRequiredValueKeyCollectionForChannelAndLocales */
    private $findRequiredValueKeyCollectionForChannelAndLocale;

    /** @var \DateTime[] */
    private $updatedDateByRecord = [];

    /** @var InMemoryDateRepository */
    private $dateRepository;

    public function __construct(
        InMemoryReferenceEntityRepository $referenceEntityRepository,
        InMemoryFindRequiredValueKeyCollectionForChannelAndLocales $findRequiredValueKeyCollectionForChannelAndLocale,
        InMemoryDateRepository $dateRepository
    ) {
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->findRequiredValueKeyCollectionForChannelAndLocale = $findRequiredValueKeyCollectionForChannelAndLocale;
        $this->dateRepository = $dateRepository;
    }

    public function add(Record $record): void
    {
        $this->records[] = $record;
        $this->updatedDateByRecord[(string) $record->getIdentifier()] = $this->dateRepository->getCurrentDate();
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(RecordQuery $query): IdentifiersForQueryResult
    {
        $referenceEntityFilter = $query->getFilter('reference_entity');
        $fullTextFilter = ($query->hasFilter('full_text')) ? $query->getFilter('full_text') : null;
        $codeFilter = ($query->hasFilter('code')) ? $query->getFilter('code') : null;
        $codeLabelFilter = ($query->hasFilter('code_label')) ? $query->getFilter('code_label') : null;
        $completeFilter = ($query->hasFilter('complete')) ? $query->getFilter('complete') : null;
        $updatedFilter = ($query->hasFilter('updated')) ? $query->getFilter('updated'): null;
        $attributeFilters = ($query->hasFilter('values.*')) ? $query->getValueFilters() : [];

        $records = array_values(array_filter($this->records, function (Record $record) use ($referenceEntityFilter) {
            return '' === $referenceEntityFilter['value']
                || (string) $record->getReferenceEntityIdentifier() === $referenceEntityFilter['value'];
        }));

        $referenceEntity = $this->referenceEntityRepository->getByIdentifier($records[0]->getReferenceEntityIdentifier());
        $attributeAsLabel = $referenceEntity->getAttributeAsLabelReference()->normalize();

        $records = array_values(array_filter($records, function (Record $record) use ($fullTextFilter, $query, $attributeAsLabel) {
            $labels = $this->getLabelsFromValues($record->getValues()->normalize(), $attributeAsLabel);

            return null === $fullTextFilter
                || '' === $fullTextFilter['value']
                || false !== strpos((string) $record->getCode(), $fullTextFilter['value'])
                || (array_key_exists($query->getLocale(), $labels) && false !== strpos($labels[$query->getLocale()], $fullTextFilter['value']));
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

        $records = array_values(array_filter($records, function (Record $record) use ($completeFilter, $query): bool {
            if (null === $completeFilter) {
                return true;
            }

            $channel = isset($completeFilter['context']['channel']) ? $completeFilter['context']['channel'] : $query->getChannel();
            $locales = isset($completeFilter['context']['locales']) ? $completeFilter['context']['locales'] : [$query->getLocale()];

            $requiredValueKeyCollection = ($this->findRequiredValueKeyCollectionForChannelAndLocale)(
                $record->getReferenceEntityIdentifier(),
                ChannelIdentifier::fromCode($channel),
                LocaleIdentifierCollection::fromNormalized($locales)
            );

            $recordValues = $record->getValues();
            $requiredValuesComplete = 0;
            $requiredValues = 0;

            foreach ($requiredValueKeyCollection as $requiredValueKey) {
                $requiredValues++;
                if (null !== $recordValues->findValue($requiredValueKey)) {
                    $requiredValuesComplete++;
                }
            }

            if ($requiredValues > 0) {
                $completeness['complete'] = $requiredValuesComplete;
                $completeness['required'] = $requiredValues;
            }

            $isComplete = ($requiredValuesComplete === $requiredValues);

            return $completeFilter['value'] ? $isComplete : !$isComplete;
        }));

        $records = array_values(array_filter($records, function (Record $record) use ($codeLabelFilter, $attributeAsLabel) {
            if (null === $codeLabelFilter) {
                return true;
            }

            $labelsFromValues = $this->getLabelsFromValues($record->getValues()->normalize(), $attributeAsLabel);
            $field = sprintf('%s %s', $record->getCode()->normalize(), implode(' ', $labelsFromValues));

            return false !== strpos($field, $codeLabelFilter['value']);
        }));

        $records = array_values(array_filter($records, function (Record $record) use ($updatedFilter) {
            if (null === $updatedFilter) {
                return true;
            }

            $updatedSinceDate = (new \DateTime($updatedFilter['value']))->getTimestamp();
            $recordDate = ($this->updatedDateByRecord[(string) $record->getIdentifier()])->getTimestamp();

            return $recordDate >= $updatedSinceDate;
        }));

        $records = array_values(array_filter($records, function (Record $record) use ($attributeFilters) {
            if (empty($attributeFilters)) {
                return true;
            }

            foreach ($attributeFilters as $attributeFilter) {
                $attributeIdentifier = substr($attributeFilter['field'], 7);
                $value = $record->getValues()->findValue(ValueKey::createFromNormalized($attributeIdentifier));

                if (null === $value) {
                    return false;
                }

                $data = (is_array($value->getData()->normalize())) ? $value->getData()->normalize() : [$value->getData()->normalize()];

                return $attributeFilter['value'] === $data;
            }
        }));

        if ($query->isPaginatedUsingSearchAfter()) {
            $searchAfterCode = $query->getSearchAfterCode();
            $records = array_values(array_filter($records, function (Record $record) use ($searchAfterCode): bool {
                return null === $searchAfterCode
                    || strcasecmp((string) $record->getCode(), $searchAfterCode) > 0;
            }));

            usort($records, function ($firstRecord, $secondRecord) {
                return strcasecmp((string) $firstRecord->getCode(), (string) $secondRecord->getCode());
            });

            $records = array_slice($records, 0, $query->getSize());
        }

        $identifiers = array_map(function (Record $record): string {
            return (string) $record->getIdentifier();
        }, $records);
        $result = new IdentifiersForQueryResult($identifiers, count($records));

        return $result;
    }

    private function getLabelsFromValues(array $valueCollection, string $attributeAsLabel): array
    {
        return array_reduce(
            $valueCollection,
            function (array $labels, array $value) use ($attributeAsLabel) {
                if ($value['attribute'] === $attributeAsLabel) {
                    $localeCode = $value['locale'];
                    $label = (string) $value['data'];
                    $labels[$localeCode] = $label;
                }

                return $labels;
            },
            []
        );
    }

    /**
     * @return Record[]
     */
    public function getRecords(): array
    {
        return $this->records;
    }
}
