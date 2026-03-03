<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Query\GetCategory;

use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\ImageDataValue;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\PermissionCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Domain\ValueObject\ValueCollection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type ImageData from ImageDataValue
 *
 * @phpstan-type NormalizedValue array{data: string|ImageData|null, channel: string|null, locale: string|null, attribute_code: string}
 * @phpstan-type NormalizedInternalApiCategory array{
 *     id: int|null,
 *     parent: int|null,
 *     root_id: int | null,
 *     template_uuid: string | null,
 *     properties: array{
 *       code: string,
 *       labels: array<string, string>|\stdClass
 *     },
 *     attributes: array<string, NormalizedValue> | null,
 *     permissions: array<string, array<int>>|null,
 *     isRoot: bool,
 *     root: array{
 *       id: int|null,
 *       parent: int|null,
 *       root_id: int | null,
 *       template_uuid: string | null,
 *       properties: array{
 *         code: string,
 *         labels: array<string, string>|\stdClass
 *       },
 *       attributes: array<string, NormalizedValue> | null,
 *       permissions: array<string, array<int>>|null,
 *       isRoot: bool,
 *       root: mixed|null
 *     }|null
 * }
 */
class InternalApiCategory
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
        private bool $isRoot = false,
        private ?InternalApiCategory $root = null,
    ) {
    }

    public static function fromCategory(Category $category, ?Category $root): self
    {
        return new self(
            id: $category->getId(),
            code: $category->getCode(),
            templateUuid: $category->getTemplateUuid(),
            labels: $category->getLabels(),
            parentId: $category->getParentId(),
            rootId: $category->getRootId(),
            attributes: $category->getAttributes(),
            permissions: $category->getPermissions(),
            isRoot: $category->isRoot(),
            root: ($root) ? InternalApiCategory::fromCategory($root, null) : null,
        );
    }

    /**
     * @phpstan-return NormalizedInternalApiCategory
     */
    public function normalize(): array
    {
        return [
            'id' => $this->id?->getValue(),
            'parent' => $this->parentId?->getValue(),
            'root_id' => $this->rootId?->getValue(),
            'template_uuid' => $this->templateUuid?->getValue(),
            'properties' => [
                'code' => (string) $this->code,
                'labels' => empty($this->labels->getTranslations()) ? (object) [] : $this->labels->normalize(),
            ],
            'attributes' => $this->attributes?->normalize(),
            'permissions' => $this->permissions?->normalize(),
            'isRoot' => $this->isRoot,
            'root' => ($this->root) ? $this->root->normalize() : null,
        ];
    }
}
