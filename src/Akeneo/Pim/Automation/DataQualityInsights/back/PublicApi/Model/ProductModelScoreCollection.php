<?php

declare(strict_types=1);


namespace Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelScoreCollection
{
    public function __construct(public array $productModelScores = [])
    {
    }

    public function getProductModelScoreByChannelAndLocale(string $channel, string $locale): ?ProductModelScore
    {
        return $this->productModelScores[$channel][$locale] ?? null;
    }
}
