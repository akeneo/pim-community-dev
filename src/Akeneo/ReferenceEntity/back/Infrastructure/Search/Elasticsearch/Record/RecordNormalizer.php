<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeysToIndexForAllChannelsAndLocalesInterface;
use Akeneo\ReferenceEntity\Domain\Query\Locale\FindActivatedLocalesInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\SearchableRecordItem;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\SqlFindSearchableRecords;

/**
 * Generates a representation of a record for the search engine.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordNormalizer implements RecordNormalizerInterface
{
    private const IDENTIFIER = 'identifier';
    private const CODE = 'code';
    private const REFERENCE_ENTITY_CODE = 'reference_entity_code';
    private const RECORD_FULL_TEXT_SEARCH = 'record_full_text_search';
    private const UPDATED_AT = 'updated_at';
    private const RECORD_CODE_LABEL_SEARCH = 'record_code_label_search';
    private const COMPLETE_VALUE_KEYS = 'complete_value_keys';
    private const VALUES_FIELD = 'values';
    private const ES_TERM_MAX_LENGTH = 32766;

    public function __construct(
        private FindValueKeysToIndexForAllChannelsAndLocalesInterface $findValueKeysToIndexForAllChannelsAndLocales,
        private SqlFindSearchableRecords $findSearchableRecords,
        private FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType,
        private FindActivatedLocalesInterface $findActivatedLocales,
    ) {
    }

    public function normalizeRecord(RecordIdentifier $recordIdentifier): array
    {
        $searchableRecordItem = $this->findSearchableRecords->byRecordIdentifier($recordIdentifier);
        if (!$searchableRecordItem instanceof SearchableRecordItem) {
            throw RecordNotFoundException::withIdentifier($recordIdentifier);
        }
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($searchableRecordItem->referenceEntityIdentifier);
        $matrixWithValueKeys = $this->findValueKeysToIndexForAllChannelsAndLocales->find($referenceEntityIdentifier);
        $fullTextMatrix = $this->fillMatrix($matrixWithValueKeys, $searchableRecordItem);
        $codeLabelMatrix = $this->createCodeLabelMatrix($searchableRecordItem);
        $filledValueKeysMatrix = $this->generateFilledValueKeys($searchableRecordItem);
        $filterableValues = $this->generateFilterableValues($searchableRecordItem);

        return $this->normalize(
            $searchableRecordItem,
            $fullTextMatrix,
            $codeLabelMatrix,
            $filledValueKeysMatrix,
            $filterableValues
        );
    }

    public function normalizeRecordsByReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier): \Iterator
    {
        $matrixWithValueKeys = $this->findValueKeysToIndexForAllChannelsAndLocales->find($referenceEntityIdentifier);
        $searchableRecordItems = $this->findSearchableRecords->byReferenceEntityIdentifier($referenceEntityIdentifier);
        foreach ($searchableRecordItems as $searchableRecordItem) {
            $fullTextMatrix = $this->fillMatrix($matrixWithValueKeys, $searchableRecordItem);
            $codeLabelMatrix = $this->createCodeLabelMatrix($searchableRecordItem);
            $filledValueKeysMatrix = $this->generateFilledValueKeys($searchableRecordItem);
            $valueKeysToFilterOn = $this->generateFilterableValues($searchableRecordItem);

            yield $this->normalize(
                $searchableRecordItem,
                $fullTextMatrix,
                $codeLabelMatrix,
                $filledValueKeysMatrix,
                $valueKeysToFilterOn
            );
        }
    }

    private function createCodeLabelMatrix(SearchableRecordItem $searchableRecordItem): array
    {
        $matrix = [];

        $activatedLocales = $this->findActivatedLocales->findAll();

        foreach ($activatedLocales as $localeCode) {
            $label = array_key_exists($localeCode, $searchableRecordItem->labels) ? $searchableRecordItem->labels[$localeCode] : '';
            $matrix[$localeCode] = trim(sprintf('%s %s', $searchableRecordItem->code, $label));
        }

        return $matrix;
    }

    private function fillMatrix(array $matrix, SearchableRecordItem $searchableRecordItem): array
    {
        $searchRecordListMatrix = [];
        foreach ($matrix as $channelCode => $valueKeysPerLocales) {
            foreach ($valueKeysPerLocales as $localeCode => $valueKeys) {
                $searchRecordListMatrix[$channelCode][$localeCode] = $this->concatenateDataToIndex($searchableRecordItem, $valueKeys);
            }
        }

        return $searchRecordListMatrix;
    }

    private function concatenateDataToIndex(SearchableRecordItem $searchableRecordItem, array $valueKeys): string
    {
        $valuesToIndex = array_intersect_key($searchableRecordItem->values, array_flip($valueKeys));
        $dataToIndex = array_map(
            static fn (array $value) => $value['data'],
            $valuesToIndex
        );

        $stringToIndex = implode(' ', $dataToIndex);
        $cleanedData = str_replace(["\r", "\n"], " ", $stringToIndex);
        $cleanedData = strip_tags(html_entity_decode($cleanedData));

        $prefixedData = sprintf('%s %s', $searchableRecordItem->code, $cleanedData);

        if (strlen($prefixedData) > self::ES_TERM_MAX_LENGTH) {
            $prefixedData = substr($prefixedData, 0, self::ES_TERM_MAX_LENGTH);
        }

        return $prefixedData;
    }

    private function generateFilledValueKeys(SearchableRecordItem $searchableRecordItem): array
    {
        return array_fill_keys(array_keys($searchableRecordItem->values), true);
    }

    private function normalize(
        SearchableRecordItem $searchableRecordItem,
        array $fullTextMatrix,
        array $codeLabelMatrix,
        array $filledValueKeysMatrix,
        array $filterableValues
    ): array {
        return [
            self::IDENTIFIER => $searchableRecordItem->identifier,
            self::CODE => $searchableRecordItem->code,
            self::REFERENCE_ENTITY_CODE => $searchableRecordItem->referenceEntityIdentifier,
            self::RECORD_FULL_TEXT_SEARCH => $fullTextMatrix,
            self::RECORD_CODE_LABEL_SEARCH => $codeLabelMatrix,
            self::UPDATED_AT => $searchableRecordItem->updatedAt->getTimestamp(),
            self::COMPLETE_VALUE_KEYS => $filledValueKeysMatrix,
            self::VALUES_FIELD => $filterableValues,
        ];
    }

    private function generateFilterableValues(SearchableRecordItem $searchableRecordItem): array
    {
        $valueKeys = $this->findValueKeysByAttributeType->find(
            ReferenceEntityIdentifier::fromString($searchableRecordItem->referenceEntityIdentifier),
            [
                OptionAttribute::ATTRIBUTE_TYPE,
                OptionCollectionAttribute::ATTRIBUTE_TYPE,
                RecordAttribute::ATTRIBUTE_TYPE,
                RecordCollectionAttribute::ATTRIBUTE_TYPE,
            ]
        );
        $result = [];
        foreach ($valueKeys as $valueKey) {
            if (isset($searchableRecordItem->values[$valueKey])) {
                $result[$valueKey] = $searchableRecordItem->values[$valueKey]['data'];
            }
        }

        return $result;
    }
}
