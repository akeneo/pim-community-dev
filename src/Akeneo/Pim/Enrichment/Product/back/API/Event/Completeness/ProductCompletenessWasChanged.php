<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Event\Completeness;

use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductCompletenessWasChanged
{
    public function __construct(
        private ProductUuid $productUuid,
        private \DateTimeImmutable $changedAt,
        private string $channelCode,
        private string $localeCode,
        private ?int $previousRequiredAttributesCount,
        private int $newRequiredAttributesCount,
        private ?int $previousMissingAttributesCount,
        private int $newMissingAttributesCount,
        private ?int $previousRatio,
        private int $newRatio,
    ) {
    }

    public function productUuid(): ProductUuid
    {
        return $this->productUuid;
    }

    public function changedAt(): \DateTimeImmutable
    {
        return $this->changedAt;
    }

    public function channelCode(): string
    {
        return $this->channelCode;
    }

    public function localeCode(): string
    {
        return $this->localeCode;
    }

    public function previousRequiredAttributesCount(): ?int
    {
        return $this->previousRequiredAttributesCount;
    }

    public function newRequiredAttributesCount(): int
    {
        return $this->newRequiredAttributesCount;
    }

    public function previousMissingAttributesCount(): ?int
    {
        return $this->previousMissingAttributesCount;
    }

    public function newMissingAttributesCount(): int
    {
        return $this->newMissingAttributesCount;
    }

    public function previousRatio(): ?int
    {
        return $this->previousRatio;
    }

    public function newRatio(): int
    {
        return $this->newRatio;
    }
}
