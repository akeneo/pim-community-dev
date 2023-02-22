<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\Currency;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetChannelCurrenciesQueryInterface
{
    /**
     * @return array<string>
     */
    public function execute(string $channelCode): array;
}
