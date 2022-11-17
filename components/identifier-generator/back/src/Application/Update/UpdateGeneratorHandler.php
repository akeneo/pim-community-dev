<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Update;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\CommandValidatorInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateGeneratorHandler
{
    public function __construct(
        private IdentifierGeneratorRepository $identifierGeneratorRepository,
        private CommandValidatorInterface $validator
    ) {
    }

    public function __invoke(UpdateGeneratorCommand $command): void
    {
        $this->validator->validate($command);

        $identifierGenerator = $this->identifierGeneratorRepository->get($command->code);
        if (null === $identifierGenerator) { // Already check by constraint
            return;
        }
        $identifierGenerator->setDelimiter(Delimiter::fromString($command->delimiter));
        $identifierGenerator->setLabelCollection(LabelCollection::fromNormalized($command->labels));
        $identifierGenerator->setTarget(Target::fromString($command->target));
        $identifierGenerator->setStructure(Structure::fromNormalized($command->structure));

        $this->identifierGeneratorRepository->update($identifierGenerator);
    }
}
