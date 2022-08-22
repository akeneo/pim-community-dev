<?php

namespace Akeneo\UserManagement\Domain\Model;

class Group
{
    public const DEFAULT_TYPE = 'default';

    private function __construct(
        private int $id,
        private string $name,
        private string $type,
        private array $defaultPermissions,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isDefault(): bool
    {
        return self::DEFAULT_TYPE === $this->type;
    }

    public function getDefaultPermissions(): array
    {
        return $this->defaultPermissions;
    }

    public static function createFromDatabase(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            type: $data['type'] ?? self::DEFAULT_TYPE,
            defaultPermissions: json_decode($data['default_permissions'], true) ?? [],
        );
    }
}
