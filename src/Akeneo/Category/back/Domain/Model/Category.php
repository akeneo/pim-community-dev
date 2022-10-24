<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Model;

use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\PermissionCollection;
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
        private ?LabelCollection $labels = null,
        private ?CategoryId $parentId = null,
        private ?CategoryId $rootId = null,
        private ?ValueCollection $attributes = null,
        private ?PermissionCollection $permissions = null,
        private ?Template $template = null
    ) {
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

    public function getLabel(string $localeCode): string
    {
        $label = $this->labels->getTranslation($localeCode);

        if (!$label) {
            return '[' . $this->code . ']';
        }

        return $label;
    }

    public function setLabel(string $localeCode, string $label): void
    {
        $this->labels->setTranslation($localeCode, $label);
    }

    public function setAttributes(ValueCollection $attributes): void
    {
        $this->attributes = $attributes;
    }

    public function getTemplate(): ?Template
    {
        return $this->template;
    }

    /**
     * @return array{
     *     id: int|null,
     *     parent: int|null,
     *     root_id: int | null,
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
            'properties' => [
                'code' => (string) $this->getCode(),
                'labels' => $this->getLabels()?->normalize(),
            ],
            'attributes' => $this->getAttributes()?->normalize(),
            'permissions' => $this->getPermissions()?->normalize(),
            'template' => $this->getTemplate()?->normalize(),
        ];
    }
}
