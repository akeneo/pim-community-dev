<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SetMetricValue implements ValueUserIntent
{
    public function __construct(
        private string $attributeCode,
        private ?string $channelCode,
        private ?string $localeCode,
        private string|float|int $amount,
        private string $unit
    ) {
    }

    public function attributeCode(): string
    {
        return $this->attributeCode;
    }

    /**
     * @return array<string, string>
     */
    public function value(): array
    {
        return [
            'amount' => (string) $this->amount,
            'unit' => $this->unit,
        ];
    }

    public function channelCode(): ?string
    {
        return $this->channelCode;
    }

    public function localeCode(): ?string
    {
        return $this->localeCode;
    }
}
