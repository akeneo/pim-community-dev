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
    private const RECORD_FULL_TEXT_SEARCH = 'record_full_text_search';
    private const UPDATED_AT = 'updated_at';
    private const RECORD_CODE_LABEL_SEARCH = 'record_code_label_search';

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
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($searchableRecordItem->referenceEntityIdentifier);
        $matrixWithValueKeys = $this->generateSearchMatrixWithValueKeys($referenceEntityIdentifier);
        $fullTextMatrix = $this->fillMatrix($matrixWithValueKeys, $searchableRecordItem);
        $codeLabelMatrix = $this->createCodeLabelMatrix($searchableRecordItem);

        return [
            self::IDENTIFIER               => $searchableRecordItem->identifier,
            self::CODE                     => $searchableRecordItem->code,
            self::REFERENCE_ENTITY_CODE    => $searchableRecordItem->referenceEntityIdentifier,
            self::RECORD_FULL_TEXT_SEARCH  => $fullTextMatrix,
            self::RECORD_CODE_LABEL_SEARCH => $codeLabelMatrix,
            self::UPDATED_AT               => date_create('now')->format('Y-m-d'),
        ];
    }

    public function normalizeRecordsByReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier): \Iterator
    {
        $matrixWithValueKeys = $this->generateSearchMatrixWithValueKeys($referenceEntityIdentifier);
        $searchableRecordItems = $this->findSearchableRecords->byReferenceEntityIdentifier($referenceEntityIdentifier);
        foreach ($searchableRecordItems as $searchableRecordItem) {
            $fullTextMatrix = $this->fillMatrix($matrixWithValueKeys, $searchableRecordItem);
            $codeLabelMatrix = $this->createCodeLabelMatrix($searchableRecordItem);

            yield [
                self::IDENTIFIER               => $searchableRecordItem->identifier,
                self::CODE                     => $searchableRecordItem->code,
                self::REFERENCE_ENTITY_CODE    => $searchableRecordItem->referenceEntityIdentifier,
                self::RECORD_FULL_TEXT_SEARCH  => $fullTextMatrix,
                self::RECORD_CODE_LABEL_SEARCH => $codeLabelMatrix,
                self::UPDATED_AT               => date_create('now')->format('Y-m-d'),
            ];
        }
    }

    private function generateSearchMatrixWithValueKeys(ReferenceEntityIdentifier $referenceEntityIdentifier): array
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

    private function createCodeLabelMatrix(SearchableRecordItem $searchableRecordItem): array
    {
        $matrix = [];

        foreach ($searchableRecordItem->labels as $localeCode => $label) {
            $matrix[$localeCode] = sprintf('%s %s', $searchableRecordItem->code, $label);
        }

        return $matrix;
    }

    private function fillMatrix(array $matrix, SearchableRecordItem $searchableRecordItem): array
    {
        $searchRecordListMatrix = [];
        foreach ($matrix as $channelCode => $valueKeysPerLocales) {
            foreach ($valueKeysPerLocales as $localeCode => $valueKeys) {
                $indexedValues = $this->concatenateDataToIndex($searchableRecordItem, $valueKeys);
                $searchRecordListMatrix[$channelCode][$localeCode] = sprintf(
                    '%s %s %s', $searchableRecordItem->code,
                    $searchableRecordItem->labels[$localeCode] ?? '',
                    $indexedValues
                );
            }
        }

        return $searchRecordListMatrix;
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

        $stringToIndex = implode(' ', $dataToIndex);
        $cleanedData = str_replace(["\r", "\n"], " ", $stringToIndex);
        $cleanedData = strip_tags(html_entity_decode($cleanedData));

        return $cleanedData;
    }
}
