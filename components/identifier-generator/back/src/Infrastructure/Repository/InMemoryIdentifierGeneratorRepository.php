<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryIdentifierGeneratorRepository implements IdentifierGeneratorRepository
{
    /** @var array<string, IdentifierGenerator> */
    public array $generators = [];

    public function save(IdentifierGenerator $identifierGenerator): void
    {
        $this->generators[$identifierGenerator->code()->asString()] = $identifierGenerator;
    }

    public function get(string $identifierGeneratorCode): ?IdentifierGenerator
    {
        return $this->generators[$identifierGeneratorCode] ?? null;
    }
}
