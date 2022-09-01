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

    /**
     * @param array<string, array<int>>|null $permissions
     */
    public static function fromArray(?array $permissions): self
    {
        return new self($permissions);
    }

    /** @return array<int> */
    public function getViewUserGroups(): array
    {
        return $this->permissions['view'];
    }

    /** @return array<int> */
    public function getEditUserGroups(): array
    {
        return $this->permissions['edit'];
    }

    /** @return array<int> */
    public function getOwnUserGroups(): array
    {
        return $this->permissions['own'];
    }

    /** @return array<string, array<int>>|null */
    public function normalize(): ?array
    {
        return $this->permissions;
    }
}
