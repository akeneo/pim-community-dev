<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Product\Api\Command\UserIntent;

final class SetNumberValue implements ValueUserIntent
{
    public function __construct(
        private string $attributeCode,
        private ?string $localeCode,
        private ?string $channelCode,
        private int $value
    )
    {
    }

    public function attributeCode(): string
    {
        return $this->attributeCode;
    }

    public function value(): int
    {
        return $this->value;
    }

    public function localeCode(): ?string
    {
        return $this->localeCode;
    }

    public function channelCode(): ?string
    {
        return $this->channelCode;
    }
}
