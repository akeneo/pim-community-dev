<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\Query;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetProductCompletenessRatio
{
    public function forChannelCodeAndLocaleCode(int $productId, string $channelCode, string $localeCode): ?int;
}
