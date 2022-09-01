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
        private ?LabelCollection $labelCollection = null,
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

    public function getLabelCollection(): ?LabelCollection
    {
        return $this->labelCollection;
    }

    public function getParentId(): ?CategoryId
    {
        return $this->parentId;
    }

    public function getValueCollection(): ?ValueCollection
    {
        return $this->attributes;
    }

    public function getPermissionCollection(): ?PermissionCollection
    {
        return $this->permissions;
    }

    public function setLabel(string $localeCode, string $label): void
    {
        $this->labelCollection->setLabel($localeCode, $label);
    }

    public function setValueCollection(ValueCollection $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array{
     *     id: int,
     *     code: string,
     *     parent: int|null,
     *     labels: array<string, string>,
     *     attributes: array<string, array<string, mixed>> | null,
     *     permissions: array<string, array<int>>|null
     * }
     */
    public function normalize(): array
    {
        return [
            'id' => $this->getId()?->getValue(),
            'code' => (string) $this->getCode(),
            'labels' => $this->getLabelCollection()?->normalize(),
            'parent' => $this->getParentId()?->getValue(),
            'values' => $this->getValueCollection()?->normalize(),
            'permissions' => $this->getPermissionCollection()?->normalize(),
        ];
    }
}
