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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

final class ProductAxesRates
{
    /** @var ProductId */
    private $productId;

    /** @var array */
    private $axesRates;

    public function __construct(ProductId $productId, array $axesRates)
    {
        $this->productId = $productId;
        $this->axesRates = $this->mapRates(function ($rate) {
            return array_map('intval', $rate);
        }, $axesRates);
    }

    public function getRanks(): array
    {
        return $this->mapRates(function ($rates) {
            return $rates['rank'];
        }, $this->axesRates);
    }

    public function getValues(): array
    {
        return $this->mapRates(function ($rates) {
            return $rates['value'];
        }, $this->axesRates);
    }

    private function mapRates(callable $callback, array $rates): array
    {
        $mappedRates = [];
        foreach ($rates as $key => $values) {
            if (!is_array($values)) {
                throw new \InvalidArgumentException('Product axes rates are malformed');
            }

            $mappedRates[$key] = array_key_exists('rank', $values) && array_key_exists('value', $values)
                ? $callback($values)
                : $this->mapRates($callback, $values);
        }

        return $mappedRates;
    }
}
