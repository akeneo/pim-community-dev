<?php

declare(strict_types=1);

namespace Akeneo\Category\ServiceApi;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Category
{
    /**
     * @param array<string, string>|null $labels
     * @param array<string, array<string, mixed>>|null $attributes
     * @param array<string, array<int>>|null $permissions
     */
    public function __construct(
        private int $id,
        private string $code,
        private ?array $labels = null,
        private ?int $parent = null,
        private ?array $attributes = null,
        private ?array $permissions = null,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    /** @return array<string, string>|null */
    public function getLabels(): ?array
    {
        return $this->labels;
    }

    public function getParent(): ?int
    {
        return $this->parent;
    }

    /** @return array<string, array<string, mixed>>|null */
    public function getAttributes(): ?array
    {
        return $this->attributes;
    }

    /** @return array<string, array<int>>|null */
    public function getPermissions(): ?array
    {
        return $this->permissions;
    }
}
