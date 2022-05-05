<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Domain\Model;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Catalog implements \JsonSerializable
{
    public function __construct(
        private string $id,
        private string $name,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param array{id: string, name: string} $values
     */
    public static function fromSerialized(array $values): self
    {
        return new self(
            $values['id'],
            $values['name'],
        );
    }

    /**
     * @return array{id: string, name: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
