<?php

declare(strict_types=1);

namespace Akeneo\Category\Api\Command\UserIntents;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SetLabel implements UserIntent, LocalizeUserIntent
{
    public function __construct(
        private string $localeCode,
        private ?string $label,
    ) {
        if (empty($this->label)) {
            $this->label = null;
        }
    }

    public function localeCode(): string
    {
        return $this->localeCode;
    }

    public function label(): ?string
    {
        return $this->label;
    }
}
