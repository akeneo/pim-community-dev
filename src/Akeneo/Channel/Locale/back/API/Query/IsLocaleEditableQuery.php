<?php

declare(strict_types=1);

namespace Akeneo\Channel\Locale\API\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IsLocaleEditableQuery
{
    public function __construct(private string $localeCode, private int $userId)
    {
    }

    public function localeCode(): string
    {
        return $this->localeCode;
    }

    public function userId(): int
    {
        return $this->userId;
    }
}
