<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Family;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\CompletenessProductMask;
use Akeneo\Pim\Enrichment\Component\Product\Model\Projection\ProductCompletenessWithMissingAttributeCodes;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class CompletenessFamilyMaskPerChannelAndLocale
{
    /**
     * This separator should not be allowed in attribute codes
     *
     * @var string
     */
    private const ATTRIBUTE_CHANNEL_LOCALE_SEPARATOR = '-';

    /** @var string */
    private $channelCode;

    /** @var string */
    private $localeCode;

    /**
     * ['name-ecommerce-en_US', 'sku-<all_channel>-<all_locales>', ...]
     *
     * @var string[]
     */
    private $mask;

    public function __construct(string $channelCode, string $localeCode, array $mask)
    {
        $this->channelCode = $channelCode;
        $this->localeCode = $localeCode;
        $this->mask = $mask;
    }

    public function channelCode(): string
    {
        return $this->channelCode;
    }

    public function localeCode(): string
    {
        return $this->localeCode;
    }

    public function mask(): array
    {
        return $this->mask;
    }

    public function productCompleteness(CompletenessProductMask $completenessProductMask)
    {
        $difference = array_diff($this->mask, $completenessProductMask->mask());

        $missingAttributeCodes = array_map(function (string $mask) : string {
            return substr($mask, 0, strpos($mask, self::ATTRIBUTE_CHANNEL_LOCALE_SEPARATOR));
        }, $difference);

        return new ProductCompletenessWithMissingAttributeCodes(
            $this->channelCode,
            $this->localeCode,
            count($this->mask),
            $missingAttributeCodes
        );
    }
}
