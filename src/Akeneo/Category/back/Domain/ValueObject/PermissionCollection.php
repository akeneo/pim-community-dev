<?php

namespace Akeneo\Category\Domain\ValueObject;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class PermissionCollection
{
    private function __construct(private ?array $permissions)
    {
    }

    public static function fromArray(array $permissions): self
    {
        return new self($permissions);
    }

    public function isViewable(): bool
    {
        return $this->permissions['view_items'] === '1';
    }

    public function isEditable(): bool
    {
        return $this->permissions['edit_items'] === '1';
    }

    public function isOwned(): bool
    {
        return $this->permissions['own_items'] === '1';
    }
}
