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
     *     value_collection: string|null,
     *     permissions: string|null,
     *     template_uuid: string|null
     * } $category
     */
    public static function fromDatabase(array $category): self
    {
        $id = new CategoryId((int) $category['id']);
        $code = new Code($category['code']);
        $labelCollection = $category['translations'] ?
            LabelCollection::fromArray(
                json_decode($category['translations'], true, 512, JSON_THROW_ON_ERROR),
            ) : null;
        $parentId = $category['parent_id'] ? new CategoryId((int) $category['parent_id']) : null;
        $rootId = $category['root_id'] ? new CategoryId((int) $category['root_id']) : null;
        $attributes = $category['value_collection'] ?
                ValueCollection::fromArray(json_decode($category['value_collection'], true)) : null;
        $permissions = isset($category['permissions']) && $category['permissions'] ?
            PermissionCollection::fromArray(json_decode($category['permissions'], true)) : null;
        $templateUuid = isset($category['template_uuid']) ? TemplateUuid::fromString($category['template_uuid']) : null;

        return new self($id, $code, $templateUuid, $labelCollection, $parentId, $rootId, $attributes, $permissions);
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

    public function isRoot(): bool
    {
        // supposedly equivalent conditions, belt and braces
        return $this->parentId === null || $this->rootId === $this->id;
    }

    public function getAttributes(): ?ValueCollection
    {
        return $this->attributes;
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
