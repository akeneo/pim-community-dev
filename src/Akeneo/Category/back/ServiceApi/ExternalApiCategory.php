<?php

declare(strict_types=1);

namespace Akeneo\Category\ServiceApi;

use Akeneo\Category\Domain\Model\Enrichment\Category as CategoryDomain;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExternalApiCategory
{
    /**
     * @param array<string, string>|null $labels
     * @param array<string, array<string, mixed>>|null $values
     */
    public function __construct(
        private string $code,
        private ?int $parentId = null,
        private ?string $parentCode = null,
        private ?string $updated = null,
        private ?array $labels = null,
        private ?int $position = null,
        private ?string $templateCode = null,
        private ?array $values = null,
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function getParentCode(): ?string
    {
        return $this->parentCode;
    }

    public function setParentCode(string $parentCode): void
    {
        $this->parentCode = $parentCode;
    }

    public function getUpdated(): ?string
    {
        return $this->updated;
    }

    /** @return array<string, string>|null */
    public function getLabels(): ?array
    {
        return $this->labels;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getTemplateCode(): ?string
    {
        return $this->templateCode;
    }

    public function setTemplateCode(string $template): void
    {
        $this->templateCode = $template;
    }

    /** @return array<string, array<string, mixed>>|null */
    public function getValues(): ?array
    {
        return $this->values;
    }

    public static function fromDomainModel(CategoryDomain $category): self
    {
        return new self(
            code: (string) $category->getCode(),
            parentId: $category->getParentId()?->getValue(),
            parentCode: $category->getParentCode() ? (string) $category->getParentCode() : null,
            updated: $category->getUpdated()?->format('c'),
            labels: $category->getLabels()?->normalize(),
            values: $category->getAttributes()?->normalize(),
        );
    }

    /**
     * @return array{
     *     code: string,
     *     parent: string|null,
     *     updated: string|null,
     *     labels: array<string, string>|null,
     *     position: int|null,
     *     template: string|null,
     *     values: array<string, array<string, mixed>>|null
     * }
     */
    public function normalize(bool $withPosition, bool $withEnrichedAttributes): array
    {
        $normalizedCategory = [
            'code' => $this->getCode(),
            'parent' => $this->getParentCode(),
            'updated' => $this->getUpdated(),
            'labels' => $this->getLabels(),
        ];

        if ($withPosition) {
            $normalizedCategory['position'] = $this->getPosition();
        }

        if ($withEnrichedAttributes) {
            $normalizedCategory['template'] = $this->getTemplateCode();
            $normalizedCategory['values'] = $this->getValues();
        }

        return $normalizedCategory;
    }
}
