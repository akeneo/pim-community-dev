<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetIdentifierValue implements ValueUserIntent
{
    public function __construct(
        private string $attributeCode,
        private ?string $value
    ) {
    }

    public function attributeCode(): string
    {
        return $this->attributeCode;
    }

    public function localeCode(): ?string
    {
        return null;
    }

    public function channelCode(): ?string
    {
        return null;
    }

    public function value(): ?string
    {
        return $this->value;
    }
}
