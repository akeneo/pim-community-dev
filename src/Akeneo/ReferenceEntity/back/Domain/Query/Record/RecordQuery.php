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

namespace Akeneo\ReferenceEntity\Domain\Query\Record;

use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;

/**
 * Object representing a record query
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordQuery
{
    private const CHANNEL = 'channel';
    private const LOCALE  = 'locale';
    private const FILTERS = 'filters';
    private const PAGE    = 'page';
    private const SIZE    = 'size';

    /** @var string */
    private $channel;

    /** @var string */
    private $locale;

    /** @var array */
    private $filters;

    /** @var int */
    private $page;

    /** @var int */
    private $size;

    private function __construct(ChannelIdentifier $channel, LocaleIdentifier $locale, array $filters, int $page, int $size)
    {
        if (!is_array($filters)) {
            throw new \InvalidArgumentException('RecordQuery expect an array as filters');
        } else {
            foreach ($filters as $filter) {
                if (!(
                    key_exists('field', $filter) &&
                    key_exists('operator', $filter) &&
                    key_exists('value', $filter) &&
                    key_exists('context', $filter)
                )) {
                    throw new \InvalidArgumentException('RecordQuery expect an array of filters with a field, value, operator and context');
                }
            }
        }

        $this->channel = $channel;
        $this->locale  = $locale;
        $this->filters = $filters;
        $this->page    = $page;
        $this->size    = $size;
    }

    public static function createFromNormalized(array $normalizedQuery): RecordQuery
    {
        if (!(
            key_exists('channel', $normalizedQuery) &&
            key_exists('locale', $normalizedQuery) &&
            key_exists('filters', $normalizedQuery) &&
            key_exists('page', $normalizedQuery) &&
            key_exists('size', $normalizedQuery)
        )) {
            throw new \InvalidArgumentException('RecordQuery expect a channel, a locale, filters, a page and a size');
        }

        return new RecordQuery(
            ChannelIdentifier::fromCode($normalizedQuery['channel']),
            LocaleIdentifier::fromCode($normalizedQuery['locale']),
            $normalizedQuery['filters'],
            $normalizedQuery['page'],
            $normalizedQuery['size']
        );
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

    public function getSize(): int
    {
        return $this->size;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getChannel(): string
    {
        return $this->channel->normalize();
    }

    public function getLocale(): string
    {
        return $this->locale->normalize();
    }
}
