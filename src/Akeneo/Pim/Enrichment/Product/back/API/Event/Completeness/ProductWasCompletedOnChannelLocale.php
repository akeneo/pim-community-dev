<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Event\Completeness;

use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductWasCompletedOnChannelLocale
{
    public function __construct(
        private ProductUuid $productUuid,
        private \DateTimeImmutable $completedAt,
        private string $channelCode,
        private string $localeCode,
        private ?string $authorId
    ) {
    }

    public function productUuid(): ProductUuid
    {
        return $this->productUuid;
    }

    public function completedAt(): \DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function channelCode(): string
    {
        return $this->channelCode;
    }

    public function localeCode(): string
    {
        return $this->localeCode;
    }

    public function authorId(): ?string
    {
        return $this->authorId;
    }
}
