<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Domain\Model\Value;

use Webmozart\Assert\Assert;

class PriceValue implements ValueInterface
{
    private const TYPE = 'price';

    public function __construct(
        private string $value,
        private string $currencyCode,
    ) {
        Assert::stringNotEmpty($value);
        Assert::stringNotEmpty($currencyCode);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    public function normalize(): array
    {
        return [
            'type' => self::TYPE,
            'value' => $this->value,
            'currency_code' => $this->currencyCode,
        ];
    }
}
