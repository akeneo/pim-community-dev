<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductScoreCollection
{
    public array $productScores = [];

    public function __construct(array $productScores)
    {
        $this->productScores = $productScores;
    }

    public function getProductScoreByChannelAndLocale(string $channel, string $locale): ?ProductScore
    {
        return $this->productScores[$channel][$locale] ?? null;
    }
}
