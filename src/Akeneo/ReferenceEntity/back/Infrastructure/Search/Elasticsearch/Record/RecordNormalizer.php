<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindValueKeysToIndexForChannelAndLocaleInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\Query\SearchableRecordItem;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\Query\SqlFindActivatedLocalesPerChannels;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\Query\SqlFindSearchableRecords;
use Akeneo\ReferenceEntity\Infrastructure\Search\Elasticsearch\Record\Query\SqlFindValueKeysToIndexForChannelAndLocale;

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
    private const RECORD_LIST_SEARCH = 'record_list_search';
    private const UPDATED_AT = 'updated_at';

    /** @var SqlFindActivatedLocalesPerChannels */
    private $findActivatedLocalesPerChannels;

    /** @var SqlFindValueKeysToIndexForChannelAndLocale */
    private $findValueKeysToIndexForChannelAndLocale;

    /** @var SqlFindSearchableRecords */
    private $findSearchableRecords;

    public function __construct(
        SqlFindActivatedLocalesPerChannels $findActivatedLocalesPerChannels,
        FindValueKeysToIndexForChannelAndLocaleInterface $findValueKeysToIndexForChannelAndLocale,
        SqlFindSearchableRecords $findSearchableRecords
    ) {
        $this->findActivatedLocalesPerChannels = $findActivatedLocalesPerChannels;
        $this->findValueKeysToIndexForChannelAndLocale = $findValueKeysToIndexForChannelAndLocale;
        $this->findSearchableRecords = $findSearchableRecords;
    }

    public function normalizeRecord(RecordIdentifier $recordIdentifier): array
    {
        $searchableRecordItem = $this->findSearchableRecords->byRecordIdentifier($recordIdentifier);
        if (null === $searchableRecordItem) {
            throw RecordNotFoundException::withIdentifier($recordIdentifier);
        }
        $matrixWithValueKeys = $this->generateSearchMatrixWithValueKeys($searchableRecordItem->referenceEntityIdentifier);
        $filledMatrix = $this->fillMatrix($matrixWithValueKeys, $searchableRecordItem);

        return [
            self::IDENTIFIER            => $searchableRecordItem->identifier,
            self::CODE                  => $searchableRecordItem->code,
            self::REFERENCE_ENTITY_CODE => $searchableRecordItem->referenceEntityIdentifier,
            self::RECORD_LIST_SEARCH    => $filledMatrix,
            self::UPDATED_AT            => date_create('now')->format('Y-m-d'),
        ];
    }

    public function normalizeRecordsByReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier): \Generator
    {
        $matrixWithValueKeys = $this->generateSearchMatrixWithValueKeys($referenceEntityIdentifier);
        $searchableRecordItems = $this->findSearchableRecords->byReferenceEntityIdentifier($referenceEntityIdentifier);
        foreach ($searchableRecordItems as $searchableRecordItem) {
            $filledMatrix = $this->fillMatrix($matrixWithValueKeys, $searchableRecordItem);

            yield [
                self::IDENTIFIER            => $searchableRecordItem->identifier,
                self::CODE                  => $searchableRecordItem->code,
                self::REFERENCE_ENTITY_CODE => $searchableRecordItem->referenceEntityIdentifier,
                self::RECORD_LIST_SEARCH    => $filledMatrix,
                self::UPDATED_AT            => date_create('now')->format('Y-m-d'),
            ];
        }
    }

    private function generateSearchMatrixWithValueKeys($referenceEntityIdentifier): array
    {
        $matrixLocalesPerChannels = ($this->findActivatedLocalesPerChannels)();
        $matrix = [];
        foreach ($matrixLocalesPerChannels as $channelCode => $locales) {
            foreach ($locales as $localeCode) {
                $valueKeys = ($this->findValueKeysToIndexForChannelAndLocale)(
                    $referenceEntityIdentifier,
                    ChannelIdentifier::fromCode($channelCode),
                    LocaleIdentifier::fromCode($localeCode)
                )->normalize();
                $matrix[$channelCode][$localeCode] = array_flip($valueKeys);
            }
        }

        return $matrix;
    }

    private function fillMatrix(array $matrix, SearchableRecordItem $searchableRecordItem): array
    {
        foreach ($matrix as $channelCode => $valueKeysPerLocales) {
            foreach ($valueKeysPerLocales as $localeCode => $valueKeys) {
                $indexedValues = $this->concatenateDataToIndex($searchableRecordItem, $valueKeys);
                $valueIndexMatrix[$channelCode][$localeCode] = sprintf(
                    '%s %s %s', $searchableRecordItem->code,
                    $searchableRecordItem->labels[$localeCode] ?? '',
                    $indexedValues
                );
            }
        }

        return $valueIndexMatrix;
    }

    private function concatenateDataToIndex(SearchableRecordItem $searchableRecordItem, array $valueKeys): string
    {
        $valuesToIndex = array_intersect_key($searchableRecordItem->values, $valueKeys);
        $dataToIndex = array_map(
            function (array $value) {
                return $value['data'];
            },
            $valuesToIndex
        );

        return implode(' ', $dataToIndex);
    }
}
