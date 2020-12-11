<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
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
            $this->localeCollections[$channel] = new LocaleCollection(array_map(function ($locale) {
                return new LocaleCode($locale);
            }, $locales));
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
    public function next()
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
    public function valid()
    {
        return $this->iterator->valid();
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->iterator->rewind();
    }
}
