<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dashboard\GetProductsKeyIndicator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetLocalesByChannelQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductsWithAnImageQuery implements GetProductsKeyIndicator
{
    private GetLocalesByChannelQueryInterface $getLocalesByChannelQuery;

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
        $productIds = array_map(fn (ProductId $productId) => $productId->toInt(), $productIds);
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
