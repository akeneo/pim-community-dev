<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Delete;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteGeneratorHandler
{
    public function __construct(
        private readonly IdentifierGeneratorRepository $identifierGeneratorRepository,
    ) {
    }

    public function __invoke(DeleteGeneratorCommand $command): void
    {
        $this->identifierGeneratorRepository->delete($command->getIdentifierGeneratorCode());
    }
}
