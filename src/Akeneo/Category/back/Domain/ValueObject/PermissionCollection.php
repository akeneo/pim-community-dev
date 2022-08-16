<?php

namespace Akeneo\Category\Domain\ValueObject;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class PermissionCollection
{
    // @phpstan-ignore-next-line
    private function __construct(private ?array $permissions)
    {
    }

    // @phpstan-ignore-next-line
    public static function fromArray(array $permissions): self
    {
        return new self($permissions);
    }

    public function isViewable(): bool
    {
        return '1' === $this->permissions['view_items'];
    }

    public function isEditable(): bool
    {
        return '1' === $this->permissions['edit_items'];
    }

    public function isOwned(): bool
    {
        return '1' === $this->permissions['own_items'];
    }
}
