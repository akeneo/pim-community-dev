<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Model\Enrichment;

use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\PermissionCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Domain\ValueObject\ValueCollection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Category
{
    public function __construct(
        private ?CategoryId $id,
        private Code $code,
        private ?TemplateUuid $templateUuid,
        private ?LabelCollection $labels = null,
        private ?CategoryId $parentId = null,
        private ?CategoryId $rootId = null,
        private ?\DateTimeImmutable $updated = null,
        private ?ValueCollection $attributes = null,
        private ?PermissionCollection $permissions = null,
    ) {
    }

    /**
     * @param array{
     *     id: int,
     *     code: string,
     *     translations: string|null,
     *     parent_id: int|null,
     *     root_id: int|null,
     *     updated: string|null,
     *     value_collection: string|null,
     *     permissions: string|null,
     *     template_uuid: string|null
     * } $category
     */
    public static function fromDatabase(array $category): self
    {
        return new self(
            id: new CategoryId((int) $category['id']),
            code: new Code($category['code']),
            templateUuid: isset($category['template_uuid']) ? TemplateUuid::fromString($category['template_uuid']) : null,
            labels: $category['translations'] ?
                LabelCollection::fromArray(
                    json_decode($category['translations'], true, 512, JSON_THROW_ON_ERROR),
                ) : null,
            parentId: $category['parent_id'] ? new CategoryId((int) $category['parent_id']) : null,
            rootId: $category['root_id'] ? new CategoryId((int) $category['root_id']) : null,
            updated: $category['updated'] ? \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $category['updated']) : null,
            attributes: $category['value_collection'] ?
                ValueCollection::fromDatabase(json_decode($category['value_collection'], true)) : null,
            permissions: isset($category['permissions']) && $category['permissions'] ?
                PermissionCollection::fromArray(json_decode($category['permissions'], true)) : null,
        );
    }

    /**
     * @param array<string, array<int, string>> $permissions
     */
    public static function fromCategoryWithPermissions(Category $category, array $permissions): self
    {
        return new self(
            id: $category->getId(),
            code: $category->getCode(),
            templateUuid: $category->getTemplateUuid(),
            labels: $category->getLabels(),
            parentId: $category->getParentId(),
            rootId: $category->getRootId(),
            updated: $category->getUpdated(),
            attributes: $category->getAttributes(),
            permissions: $permissions ? PermissionCollection::fromArray($permissions) : null,
        );
    }

    public function getId(): ?CategoryId
    {
        return $this->id;
    }

    public function getCode(): Code
    {
        return $this->code;
    }

    public function getLabels(): ?LabelCollection
    {
        return $this->labels;
    }

    public function getParentId(): ?CategoryId
    {
        return $this->parentId;
    }

    public function getRootId(): ?CategoryId
    {
        return $this->rootId;
    }

    public function getUpdated(): ?\DateTimeImmutable
    {
        return $this->updated;
    }

    public function isRoot(): bool
    {
        // supposedly equivalent conditions, belt and braces
        return $this->parentId === null || $this->rootId === $this->id;
    }

    public function getAttributes(): ?ValueCollection
    {
        return $this->attributes;
    }

    /**
     * @return array<string> (example: [seo_meta_description|69e251b3-b876-48b5-9c09-92f54bfb528d])
     */
    public function getAttributeCodes(): array
    {
        if (null === $this->attributes) {
            return [];
        }

        $attributeCodes = [];
        foreach ($this->attributes as $attributeValues) {
            $attributeCodes[] = $attributeValues->getKey();
        }

        return array_values(array_unique($attributeCodes));
    }

    public function getPermissions(): ?PermissionCollection
    {
        return $this->permissions;
    }

    public function setLabel(string $localeCode, string $label): void
    {
        $this->labels->setTranslation($localeCode, $label);
    }

    public function setAttributes(ValueCollection $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function getTemplateUuid(): ?TemplateUuid
    {
        return $this->templateUuid;
    }

    /**
     * @return array{
     *     id: int|null,
     *     parent: int|null,
     *     root_id: int | null,
     *     template_uuid: string | null,
     *     properties: array{
     *       code: string,
     *       labels: array<string, string>|null
     *     },
     *     attributes: array<string, array<string, mixed>> | null,
     *     permissions: array<string, array<int>>|null
     * }
     */
    public function normalize(): array
    {
        return [
            'id' => $this->getId()?->getValue(),
            'parent' => $this->getParentId()?->getValue(),
            'root_id' => $this->getRootId()?->getValue(),
            'template_uuid' => $this->getTemplateUuid()?->getValue(),
            'properties' => [
                'code' => (string) $this->getCode(),
                'labels' => $this->getLabels()?->normalize(),
            ],
            'attributes' => $this->getAttributes()?->normalize(),
            'permissions' => $this->getPermissions()?->normalize(),
        ];
    }
}
