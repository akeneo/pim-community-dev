<?php

declare(strict_types=1);

namespace Akeneo\Category\API\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Category
{
    /**
     * @todo Decide between array of strings or array of objects
     *
     * @param array<string, string> $labels
     */
    public function __construct(
        private string $code,
        private ?string $parentCode,
        private \DateTimeImmutable $updated,
        private array $labels,
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getParentCode(): ?string
    {
        return $this->parentCode;
    }

    public function getUpdated(): \DateTimeImmutable
    {
        return $this->updated;
    }

    /**
     * @return array<string, string>
     */
    public function getLabels(): array
    {
        return $this->labels;
    }
}
