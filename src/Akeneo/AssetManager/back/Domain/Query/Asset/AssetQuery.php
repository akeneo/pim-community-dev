<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source filters.
 */

namespace Akeneo\AssetManager\Domain\Query\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;

/**
 * Object representing a asset query
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetQuery
{
    private const PAGINATE_USING_OFFSET = 'offset';
    private const PAGINATE_USING_SEARCH_AFTER = 'search_after';

    /** @var ChannelReference */
    private $channel;

    /** @var LocaleReference */
    private $locale;

    /** @var array */
    private $filters;

    /** @var int|null */
    private $page;

    /** @var int|null */
    private $size;

    /** @var AssetCode|null */
    private $searchAfterCode;

    /** @var string */
    private $paginationMethod;

    /**
     * If defined, the asset values will be filtered by the given channel.
     * The values without channel will not be filtered.
     *
     * @var ChannelReference
     */
    private $channelReferenceValuesFilter;

    /**
     * To filter the values by locales. The values without locale will not be filtered.
     *
     * @var LocaleIdentifierCollection
     */
    private $localeIdentifiersValuesFilter;

    private function __construct(
        ChannelReference $channel,
        LocaleReference $locale,
        array $filters,
        ChannelReference $channelReferenceValuesFilter,
        LocaleIdentifierCollection $localeIdentifiersValuesFilter,
        string $paginationMethod,
        int $size,
        ?int $page,
        ?AssetCode $searchAfterCode
    ) {
        foreach ($filters as $filter) {
            if (!(key_exists('field', $filter) &&
                key_exists('operator', $filter) &&
                key_exists('value', $filter))) {
                throw new \InvalidArgumentException('AssetQuery expect an array of filters with a field, value, operator and context');
            }
        }

        if (!in_array($paginationMethod, [self::PAGINATE_USING_OFFSET, self::PAGINATE_USING_SEARCH_AFTER])) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a supported pagination method', $paginationMethod));
        }

        $this->channel = $channel;
        $this->locale  = $locale;
        $this->filters = $filters;
        $this->page    = $page;
        $this->size    = $size;

        $this->searchAfterCode  = $searchAfterCode;
        $this->paginationMethod = $paginationMethod;

        $this->channelReferenceValuesFilter  = $channelReferenceValuesFilter;
        $this->localeIdentifiersValuesFilter = $localeIdentifiersValuesFilter;
    }

    public static function createFromNormalized(array $normalizedQuery): AssetQuery
    {
        if (!(key_exists('channel', $normalizedQuery) &&
            key_exists('locale', $normalizedQuery) &&
            key_exists('filters', $normalizedQuery) &&
            key_exists('page', $normalizedQuery) &&
            key_exists('size', $normalizedQuery))) {
            throw new \InvalidArgumentException('AssetQuery expect a channel, a locale, filters, a page and a size');
        }

        return new AssetQuery(
            ChannelReference::createfromNormalized($normalizedQuery['channel']),
            LocaleReference::createFromNormalized($normalizedQuery['locale']),
            $normalizedQuery['filters'],
            ChannelReference::noReference(),
            LocaleIdentifierCollection::empty(),
            self::PAGINATE_USING_OFFSET,
            $normalizedQuery['size'],
            $normalizedQuery['page'],
            null
        );
    }

    public static function createPaginatedQueryUsingSearchAfter(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        ChannelReference $channelReferenceValuesFilter,
        LocaleIdentifierCollection $localeIdentifiersValuesFilter,
        int $size,
        ?AssetCode $searchAfterCode,
        array $filters
    ): AssetQuery {
        $filters[] = [
            'field'    => 'asset_family',
            'operator' => '=',
            'value'    => (string) $assetFamilyIdentifier
        ];

        return new AssetQuery(
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
        AssetFamilyIdentifier $assetFamilyIdentifier,
        ChannelReference $channel,
        LocaleReference $locale,
        int $size,
        ?AssetCode $searchAfterCode,
        array $filters
    ): AssetQuery {
        $filters[] = [
            'field'    => 'asset_family',
            'operator' => '=',
            'value'    => (string) $assetFamilyIdentifier
        ];

        return new AssetQuery(
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

    public static function createNextQuery(
        AssetQuery $assetQuery,
        AssetCode $searchAfterCode
    ): AssetQuery {
        return new self(
            $assetQuery->channel,
            $assetQuery->locale,
            $assetQuery->filters,
            $assetQuery->channelReferenceValuesFilter,
            $assetQuery->localeIdentifiersValuesFilter,
            self::PAGINATE_USING_SEARCH_AFTER,
            $assetQuery->size,
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
            throw new \InvalidArgumentException(sprintf('No filter found on values'));
        }

        return $filters;
    }

    public function getFilter(string $field): array
    {
        $filter = current(array_filter($this->filters, function ($filter) use ($field) {
            return $filter['field'] === $field;
        }));

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
                if (\count($matches) > 0) {
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
}
