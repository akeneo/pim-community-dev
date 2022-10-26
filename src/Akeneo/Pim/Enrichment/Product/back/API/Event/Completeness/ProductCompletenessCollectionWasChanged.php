<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\back\API\Event\Completeness;

use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductCompletenessCollectionWasChanged
{
    /**
     * @param array<ChangedProductCompleteness> $changedProductCompletenesses
     */
    public function __construct(
        private ProductUuid $productUuid,
        private \DateTimeImmutable $changedAt,
        private array $changedProductCompletenesses,
    ) {
        Assert::allIsInstanceOf($this->changedProductCompletenesses, ChangedProductCompleteness::class);
    }

    public function productUuid(): ProductUuid
    {
        return $this->productUuid;
    }

    public function changedAt(): \DateTimeImmutable
    {
        return $this->changedAt;
    }

    /**
     * @return array<ChangedProductCompleteness>
     */
    public function changedProductCompletenesses(): array
    {
        return $this->changedProductCompletenesses;
    }
}
