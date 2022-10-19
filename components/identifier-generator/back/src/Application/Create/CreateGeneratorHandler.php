<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Create;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\CommandValidatorInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateGeneratorHandler
{
    public function __construct(
        private IdentifierGeneratorRepository $identifierGeneratorRepository,
        private CommandValidatorInterface $validator
    ) {
    }

    public function __invoke(CreateGeneratorCommand $command): void
    {
        $this->validator->validate($command);

        $identifierGenerator = new IdentifierGenerator(
            IdentifierGeneratorId::fromString($command->id),
            IdentifierGeneratorCode::fromString($command->code),
            Conditions::fromArray($command->conditions),
            Structure::fromArray($command->structure),
            LabelCollection::fromNormalized($command->labels),
            Target::fromString($command->target),
            Delimiter::fromString($command->delimiter),
        );

        $this->identifierGeneratorRepository->save($identifierGenerator);
    }
}
