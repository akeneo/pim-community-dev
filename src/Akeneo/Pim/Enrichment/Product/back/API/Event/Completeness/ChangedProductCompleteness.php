<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Event\Completeness;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ChangedProductCompleteness
{
    public function __construct(
        private string $channelCode,
        private string $localeCode,
        private ?int $previousRequiredCount,
        private int $newRequiredCount,
        private ?int $previousMissingCount,
        private int $newMissingCount,
        private ?int $previousRatio,
        private int $newRatio,
    ) {
    }

    public function channelCode(): string
    {
        return $this->channelCode;
    }

    public function localeCode(): string
    {
        return $this->localeCode;
    }

    public function previousRequiredCount(): ?int
    {
        return $this->previousRequiredCount;
    }

    public function newRequiredCount(): int
    {
        return $this->newRequiredCount;
    }

    public function previousMissingCount(): ?int
    {
        return $this->previousMissingCount;
    }

    public function newMissingCount(): int
    {
        return $this->newMissingCount;
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
