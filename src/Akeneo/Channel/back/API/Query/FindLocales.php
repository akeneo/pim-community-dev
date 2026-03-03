<?php

namespace Akeneo\Channel\API\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FindLocales
{
    public function find(string $localeCode): ?Locale;

    /**
     * @return Locale[]
     */
    public function findAllActivated(): array;
}
