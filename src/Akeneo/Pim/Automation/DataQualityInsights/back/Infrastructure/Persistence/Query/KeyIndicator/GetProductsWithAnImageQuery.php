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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetProductsKeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;

final class GetProductsWithAnImageQuery implements GetProductsKeyIndicator
{
    private $getLocalesByChannelQuery;

    public function __construct(GetLocalesByChannelQueryInterface $getLocalesByChannelQuery)
    {
        $this->getLocalesByChannelQuery = $getLocalesByChannelQuery;
    }

    public function getName(): string
    {
        return 'has_image';
    }

    public function execute(array $productIds): array
    {
        $localesByChannel = $this->getLocalesByChannelQuery->getArray();

        $result = [];
        foreach ($productIds as $productId) {
            foreach ($localesByChannel as $channel => $locales) {
                foreach ($locales as $locale) {
                    $result[$productId][$channel][$locale] = true;
                }
            }
        }

        return $result;
    }
}
