<?php

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MetricValue
{
    public function __construct(
        private string|float|int $amount,
        private string $unit
    )
    {
    }

    public function amount(): int
    {
        return (int) $this->amount;
    }

    public function unit(): string
    {
        return $this->unit;
    }
}
