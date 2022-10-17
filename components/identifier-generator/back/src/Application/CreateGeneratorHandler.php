<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Exception\ViolationsException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateGeneratorHandler
{
    public function __construct(
        private IdentifierGeneratorRepository $identifierGeneratorRepository,
        private ValidatorInterface $validator
    ) {
    }

    public function __invoke(CreateGeneratorCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if (0 < $violations->count()) {
            throw new ViolationsException($violations);
        }

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
