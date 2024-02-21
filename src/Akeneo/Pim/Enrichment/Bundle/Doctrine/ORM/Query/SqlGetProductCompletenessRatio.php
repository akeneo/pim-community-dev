<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetProductCompletenessRatio;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetProductCompletenessRatio implements GetProductCompletenessRatio
{
    public function __construct(
        private readonly GetProductCompletenesses $getProductCompletenesses
    ) {
    }

    public function forChannelCodeAndLocaleCode(UuidInterface $productUuid, string $channelCode, string $localeCode): ?int
    {
        $completenessCollection = $this->getProductCompletenesses->fromProductUuids([$productUuid], $channelCode, [$localeCode]);
        if (!$completenessCollection) {
            return null;
        }

        return $completenessCollection[$productUuid->toString()]?->getCompletenessForChannelAndLocale($channelCode, $localeCode)?->ratio();
    }
}
