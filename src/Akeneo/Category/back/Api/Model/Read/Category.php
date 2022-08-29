<?php

declare(strict_types=1);

/**
 * A GetCategoryModel represents the information returned by the GetCategoryQuery query.
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Category\Api\Model\Read;

use Akeneo\Category\Domain\Model\Category as CategoryDomain;

class Category
{
    /**
     * @param array<string, string> $labels
     * @param int|null $parentId
     * @param array<string, array<string, mixed>> $values
     * @param array<string, array<int>>|null $permissions
     */
    private function __construct(
        private int $id,
        private string $code,
        private array $labels,
        private ?int $parentId,
        private array $values,
        private ?array $permissions,
    ) {
    }

    public static function fromDomain(CategoryDomain $category): self
    {
        return new self(
            id: $category->getId()->getValue(),
            code: (string) $category->getCode(),
            labels: $category->getLabelCollection()->normalize(),
            parentId: $category->getParentId()?->getValue(),
            values: $category->getValueCollection()->normalize(),
            permissions: $category->getPermissionCollection()?->normalize(),
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return array<string, string>
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    public function getParentId(): int
    {
        return $this->parentId;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @return array<string, array<int>>|null
     */
    public function getPermissions(): array|null
    {
        return $this->permissions;
    }

    /**
     * @return array{
     *     id: int,
     *     code: string,
     *     parent: int|null,
     *     labels: array<string, string>,
     *     attributes: array<string, array<string, mixed>>,
     *     permissions: array<string, array<int>>|null
     * }
     */
    public function normalize(): array
    {
        return [
            'id' => $this->getId(),
            'code' => $this->getCode(),
            'parent' => $this->getParentId(),
            'labels' => $this->getLabels(),
            'attributes' => $this->getValues(),
            'permissions' => $this->getPermissions(),
        ];
    }
}
