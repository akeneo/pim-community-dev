<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model;

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
