<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Get;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type NormalizedIdentifierGenerator from IdentifierGenerator
 */
final class GetGeneratorsHandler
{
    public function __construct(
        private readonly IdentifierGeneratorRepository $identifierGeneratorRepository,
    ) {
    }

    /**
     * @return array<NormalizedIdentifierGenerator>
     */
    public function __invoke(GetGeneratorsQuery $query): array
    {
        $identifiersGenerators = $this->identifierGeneratorRepository->getAll();

        return \array_map(fn ($identifierGenerator) => $identifierGenerator->normalize(), $identifiersGenerators);
    }
}
