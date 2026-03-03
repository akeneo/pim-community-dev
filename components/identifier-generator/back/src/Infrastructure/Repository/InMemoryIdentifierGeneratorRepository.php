<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\CouldNotFindIdentifierGeneratorException;
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
    /** @var IdentifierGenerator[] */
    public array $generators = [];

    /**
     * {@inheritdoc}
     */
    public function save(IdentifierGenerator $identifierGenerator): void
    {
        $this->generators[] = $identifierGenerator;
    }

    public function update(IdentifierGenerator $identifierGenerator): void
    {
        $this->generators[$this->getGeneratorIndex($identifierGenerator->code()->asString())] = $identifierGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $identifierGeneratorCode): IdentifierGenerator
    {
        $index = $this->getGeneratorIndex($identifierGeneratorCode);
        if (null === $index) {
            throw new CouldNotFindIdentifierGeneratorException($identifierGeneratorCode);
        }

        return $this->generators[$index];
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
        unset($this->generators[$this->getGeneratorIndex($identifierGeneratorCode)]);
    }

    /**
     * @param string[] $identifierGeneratorCodes
     */
    public function reorder(array $identifierGeneratorCodes): void
    {
        $generators = $this->generators;
        $identifierGeneratorCodes = \array_map('mb_strtolower', $identifierGeneratorCodes);

        \usort(
            $generators,
            fn (IdentifierGenerator $generatorA, IdentifierGenerator $generatorB): int =>
            (int)\array_search($generatorA->code()->asString(), $identifierGeneratorCodes) - (int)\array_search($generatorB->code()->asString(), $identifierGeneratorCodes)
        );

        $this->generators = $generators;
    }

    private function getGeneratorIndex(string $identifierGeneratorCode): ?int
    {
        foreach ($this->generators as $i => $generator) {
            if (\mb_strtolower($generator->code()->asString()) === \mb_strtolower($identifierGeneratorCode)) {
                return $i;
            }
        }

        return null;
    }
}
