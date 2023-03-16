<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryIdentifierGeneratorRepository implements IdentifierGeneratorRepository
{
    /** @var array<string, IdentifierGenerator> */
    public array $generators = [];

    /**
     * {@inheritdoc}
     */
    public function save(IdentifierGenerator $identifierGenerator): void
    {
        $this->generators[$identifierGenerator->code()->asString()] = $identifierGenerator;
    }

    public function update(IdentifierGenerator $identifierGenerator): void
    {
        $this->generators[$identifierGenerator->code()->asString()] = $identifierGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $identifierGeneratorCode): ?IdentifierGenerator
    {
        return $this->generators[$identifierGeneratorCode] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(): array
    {
        return \array_values($this->generators);
    }

    /**
     * {@inheritdoc}
     */
    public function getNextId(): IdentifierGeneratorId
    {
        return IdentifierGeneratorId::fromString(Uuid::uuid4()->toString());
    }

    public function count(): int
    {
        return \count($this->generators);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $identifierGeneratorCode): void
    {
        unset($this->generators[$identifierGeneratorCode]);
    }
}
