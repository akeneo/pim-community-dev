<?php

declare(strict_types=1);

namespace Akeneo\Category\ServiceApi;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Category
{
    public function __construct(
        private int $id,
        private string $code,
        private array $labels,
        private ?int $parent = null,
        private ?array $values = null,
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

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function getParent(): ?int
    {
        return $this->parent;
    }

    public function getValues(): ?array
    {
        return $this->values;
    }

    public function getPermissions(): ?array
    {
        return $this->permissions;
    }
}
