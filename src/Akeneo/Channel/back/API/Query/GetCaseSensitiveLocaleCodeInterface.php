<?php

namespace Akeneo\Channel\API\Query;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCaseSensitiveLocaleCodeInterface
{
    /**
     * Returns the case sensitive locale code from any locale code
     * Example: forLocaleCode('EN_us') => 'en_US'.
     */
    public function forLocaleCode(string $localeCode): string;
}
