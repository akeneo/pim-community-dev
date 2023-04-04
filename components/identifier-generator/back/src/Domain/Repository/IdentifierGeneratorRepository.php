<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\CouldNotFindIdentifierGeneratorException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\UnableToDeleteIdentifierGeneratorException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\UnableToFetchIdentifierGeneratorException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\UnableToSaveIdentifierGeneratorException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\UnableToUpdateIdentifierGeneratorException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface IdentifierGeneratorRepository
{
    /**
     * @throws UnableToSaveIdentifierGeneratorException
     */
    public function save(IdentifierGenerator $identifierGenerator): void;

    /**
     * @throws UnableToUpdateIdentifierGeneratorException
     */
    public function update(IdentifierGenerator $identifierGenerator): void;

    /**
     * @throws UnableToFetchIdentifierGeneratorException
     * @throws CouldNotFindIdentifierGeneratorException
     */
    public function get(string $identifierGeneratorCode): IdentifierGenerator;

    /**
     * @throws UnableToFetchIdentifierGeneratorException
     * @return IdentifierGenerator[]
     */
    public function getAll(): array;

    public function getNextId(): IdentifierGeneratorId;

    public function count(): int;

    /**
     * @throws UnableToDeleteIdentifierGeneratorException
     * @throws CouldNotFindIdentifierGeneratorException
     */
    public function delete(string $identifierGeneratorCode): void;
}
