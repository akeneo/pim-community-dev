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
        private ?ValueCollection $attributes = null,
        private ?PermissionCollection $permissions = null,
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

    /**
     * @return array{
     *     id: int|null,
     *     parent: int|null,
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
            'properties' => [
                'code' => (string) $this->getCode(),
                'labels' => $this->getLabels()?->normalize(),
            ],
            'attributes' => $this->getAttributes()?->normalize(),
            'permissions' => $this->getPermissions()?->normalize(),
        ];
    }
}
