<?php

namespace Akeneo\Channel\API\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Locale
{
    public function __construct(
        private string $code,
        private bool $isActivated
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function isActivated(): bool
    {
        return $this->isActivated;
    }
}
