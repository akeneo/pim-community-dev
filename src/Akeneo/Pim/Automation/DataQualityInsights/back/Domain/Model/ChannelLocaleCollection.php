<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

/**
 * Allow iterating on the locales per channel, having a object ChannelCode as key.
 */
final class ChannelLocaleCollection implements \Iterator
{
    /** @var array */
    private $channelCodes = [];

    /** @var array */
    private $localeCollections = [];

    /** @var \Iterator */
    private $iterator;

    public function __construct(array $localesByChannel)
    {
        foreach ($localesByChannel as $channel => $locales) {
            $this->channelCodes[$channel] = new ChannelCode($channel);
            $this->localeCollections[$channel] = new LocaleCollection(array_map(fn($locale) => new LocaleCode($locale), $locales));
        }

        $this->iterator = new \ArrayIterator($this->localeCollections);
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        return $this->iterator->current();
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->iterator->next();
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->channelCodes[$this->iterator->key()] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return $this->iterator->valid();
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->iterator->rewind();
    }
}
