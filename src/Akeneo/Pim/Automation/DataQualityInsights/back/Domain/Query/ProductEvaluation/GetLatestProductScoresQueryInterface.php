<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetLatestProductScoresQueryInterface
{
    public function byProductId(ProductId $productId): ChannelLocaleRateCollection;

    /**
     * @param ProductId[] $productIds
     *
     * @return ChannelLocaleRateCollection[]
     */
    public function byProductIds(array $productIds): array;
}
