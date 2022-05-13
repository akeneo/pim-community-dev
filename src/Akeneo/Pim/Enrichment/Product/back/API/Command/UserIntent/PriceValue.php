<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class PriceValue
{
    public function __construct(
        private string|float|int $amount,
        private string $currency
    ) {
    }

    public function amount(): string
    {
        return (string) $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }
}
