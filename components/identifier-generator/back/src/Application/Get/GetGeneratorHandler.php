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
final class GetGeneratorHandler
{
    public function __construct(
        private readonly IdentifierGeneratorRepository $identifierGeneratorRepository,
    ) {
    }

    /**
     * @return NormalizedIdentifierGenerator
     */
    public function __invoke(GetGeneratorQuery $query): array
    {
        return $this->identifierGeneratorRepository
            ->get($query->getIdentifierGeneratorCode())
            ->normalize();
    }
}
