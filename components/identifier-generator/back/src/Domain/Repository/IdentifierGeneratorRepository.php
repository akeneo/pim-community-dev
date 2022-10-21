<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\UnableToFetchIdentifierGeneratorException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\UnableToSaveIdentifierGeneratorException;
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
     * @throws UnableToFetchIdentifierGeneratorException
     */
    public function get(string $identifierGeneratorCode): ?IdentifierGenerator;

    public function getNextId(): IdentifierGeneratorId;

    public function count(): int;
}
