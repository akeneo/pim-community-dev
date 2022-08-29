<?php

declare(strict_types=1);

namespace Akeneo\Category\Api\Command\UserIntents;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SetRichText implements ValueUserIntent
{
    public function __construct(
        private string $attributeUuid,
        private string $attributeCode,
        private ?string $localeCode,
        private string $value,
    ) {
    }

    public function attributeUuid(): string
    {
        return $this->attributeUuid;
    }

    public function attributeCode(): string
    {
        return $this->attributeCode;
    }

    public function localeCode(): ?string
    {
        return $this->localeCode;
    }

    public function value(): string
    {
        return $this->value;
    }
}
