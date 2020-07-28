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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\TitleSuggestion;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class IgnoredTitleSuggestion
{
    private $productId;

    private $channel;

    private $locale;

    private $titleSuggestion;

    public function __construct(ProductId $productId, ChannelCode $channel, LocaleCode $locale, TitleSuggestion $titleSuggestion)
    {
        $this->productId = $productId;
        $this->channel = $channel;
        $this->locale = $locale;
        $this->titleSuggestion = $titleSuggestion;
    }

    /**
     * @return ProductId
     */
    public function getProductId(): ProductId
    {
        return $this->productId;
    }

    /**
     * @return ChannelCode
     */
    public function getChannel(): ChannelCode
    {
        return $this->channel;
    }

    /**
     * @return LocaleCode
     */
    public function getLocale(): LocaleCode
    {
        return $this->locale;
    }

    /**
     * @return TitleSuggestion
     */
    public function getTitleSuggestion(): TitleSuggestion
    {
        return $this->titleSuggestion;
    }
}
