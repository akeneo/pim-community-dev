<?php

declare(strict_types=1);

namespace Akeneo\Category\Api\Command\UserIntents;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetLabel implements UserIntent
{
    public function __construct(
        private ?string $localeCode,
        private string $value
    ) {
    }

    public function value(): string
    {
        return $this->value;
    }

    public function localeCode(): ?string
    {
        return $this->localeCode;
    }
}
