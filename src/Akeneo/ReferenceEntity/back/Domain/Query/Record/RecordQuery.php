<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source filters.
 */

namespace Akeneo\ReferenceEntity\Domain\Query\Record;

use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

/**
 * Object representing a record query
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class RecordQuery
{
    private const PAGINATE_USING_OFFSET = 'offset';
    private const PAGINATE_USING_SEARCH_AFTER = 'search_after';

    /** @param array<{field: string, operator:string, value: string}> $filters */
    private function __construct(
        private ChannelReference $channel,
        private LocaleReference $locale,
        private array $filters,
        private ChannelReference $channelReferenceValuesFilter,
        private LocaleIdentifierCollection $localeIdentifiersValuesFilter,
        private string $paginationMethod,
        private ?int $size,
        private ?int $page,
        private ?RecordCode $searchAfterCode
    ) {
        foreach ($filters as $filter) {
            if (!(array_key_exists('field', $filter) &&
                array_key_exists('operator', $filter) &&
                array_key_exists('value', $filter))) {
                throw new \InvalidArgumentException('RecordQuery expect an array of filters with a field, value, operator and context');
            }
        }

        if (!in_array($paginationMethod, [self::PAGINATE_USING_OFFSET, self::PAGINATE_USING_SEARCH_AFTER])) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a supported pagination method', $paginationMethod));
        }
    }

    public static function createFromNormalized(array $normalizedQuery): RecordQuery
    {
        if (!(array_key_exists('channel', $normalizedQuery) &&
            array_key_exists('locale', $normalizedQuery) &&
            array_key_exists('filters', $normalizedQuery) &&
            array_key_exists('page', $normalizedQuery) &&
            array_key_exists('size', $normalizedQuery))) {
            throw new \InvalidArgumentException('RecordQuery expect a channel, a locale, filters, a page and a size');
        }

        return new RecordQuery(
            ChannelReference::createFromNormalized($normalizedQuery['channel']),
            LocaleReference::createFromNormalized($normalizedQuery['locale']),
            $normalizedQuery['filters'],
            ChannelReference::noReference(),
            LocaleIdentifierCollection::empty(),
            self::PAGINATE_USING_OFFSET,
            (int) $normalizedQuery['size'],
            (int) $normalizedQuery['page'],
            null
        );
    }

    public static function createPaginatedQueryUsingSearchAfter(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        ChannelReference $channelReferenceValuesFilter,
        LocaleIdentifierCollection $localeIdentifiersValuesFilter,
        int $size,
        ?RecordCode $searchAfterCode,
        array $filters
    ): RecordQuery {
        $filters[] = [
            'field' => 'reference_entity',
            'operator' => '=',
            'value' => (string)$referenceEntityIdentifier
        ];

        return new RecordQuery(
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            $filters,
            $channelReferenceValuesFilter,
            $localeIdentifiersValuesFilter,
            self::PAGINATE_USING_SEARCH_AFTER,
            $size,
            null,
            $searchAfterCode
        );
    }

    public static function createWithSearchAfter(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        ChannelReference $channel,
        LocaleReference $locale,
        int $size,
        ?RecordCode $searchAfterCode,
        array $filters
    ): RecordQuery {
        $filters[] = [
            'field' => 'reference_entity',
            'operator' => '=',
            'value' => (string) $referenceEntityIdentifier
        ];

        return new RecordQuery(
            $channel,
            $locale,
            $filters,
            ChannelReference::noReference(),
            LocaleIdentifierCollection::empty(),
            self::PAGINATE_USING_SEARCH_AFTER,
            $size,
            null,
            $searchAfterCode
        );
    }

    public static function createNextWithSearchAfter(
        RecordQuery $recordQuery,
        RecordCode $searchAfterCode
    ): RecordQuery {
        return new self(
            $recordQuery->channel,
            $recordQuery->locale,
            $recordQuery->filters,
            $recordQuery->channelReferenceValuesFilter,
            $recordQuery->localeIdentifiersValuesFilter,
            self::PAGINATE_USING_SEARCH_AFTER,
            $recordQuery->size,
            null,
            $searchAfterCode
        );
    }

    public function normalize(): array
    {
        return [
            'channel' => $this->channel->normalize(),
            'locale' => $this->locale->normalize(),
            'filters' => $this->filters,
            'page' => $this->page,
            'size' => $this->size
        ];
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getValueFilters(): array
    {
        $filters =  array_values(array_filter($this->filters, function ($filter) {
            preg_match('/values.*/', $filter['field'], $matches);

            return !empty($matches);
        }));

        if (empty($filters)) {
            throw new \InvalidArgumentException('No filter found on values');
        }

        return $filters;
    }

    public function getFilter(string $field): array
    {
        $filter = current(array_filter($this->filters, static fn ($filter) => $filter['field'] === $field));

        if (false === $filter) {
            throw new \InvalidArgumentException(sprintf('The query needs to contains a filter on the "%s" field', $field));
        }

        return $filter;
    }

    public function hasFilter(string $field): bool
    {
        foreach ($this->filters as $filter) {
            if ('values.*' === $field) {
                preg_match('/' . $field . '/', $filter['field'], $matches);
                if (!empty($matches)) {
                    return true;
                }
            }

            if ($filter['field'] === $field) {
                return true;
            }
        }

        return false;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function getChannel(): ?string
    {
        return $this->channel->normalize();
    }

    public function getLocale(): ?string
    {
        return $this->locale->normalize();
    }

    public function getSearchAfterCode(): ?string
    {
        return null !== $this->searchAfterCode ? (string) $this->searchAfterCode : null;
    }

    public function isPaginatedUsingOffset()
    {
        return $this->paginationMethod === self::PAGINATE_USING_OFFSET;
    }

    public function isPaginatedUsingSearchAfter()
    {
        return $this->paginationMethod === self::PAGINATE_USING_SEARCH_AFTER;
    }

    public function getChannelReferenceValuesFilter(): ChannelReference
    {
        return $this->channelReferenceValuesFilter;
    }

    public function getLocaleIdentifiersValuesFilter(): LocaleIdentifierCollection
    {
        return $this->localeIdentifiersValuesFilter;
    }

    public function getFiltersByField(string $field = null): array
    {
        return array_filter($this->filters, static fn ($filter) => $filter['field'] === $field);
    }
}
