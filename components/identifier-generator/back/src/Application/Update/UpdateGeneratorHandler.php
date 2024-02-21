<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Update;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\CommandValidatorInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateGeneratorHandler
{
    public function __construct(
        private readonly IdentifierGeneratorRepository $identifierGeneratorRepository,
        private readonly CommandValidatorInterface $validator
    ) {
    }

    public function __invoke(UpdateGeneratorCommand $command): void
    {
        $this->validator->validate($command);

        $updatedIdentifierGenerator = $this
            ->identifierGeneratorRepository
            ->get($command->code)
            ->withStructure(Structure::fromNormalized($command->structure))
            ->withConditions(Conditions::fromNormalized($command->conditions))
            ->withLabelCollection(LabelCollection::fromNormalized($command->labels))
            ->withTarget(Target::fromString($command->target))
            ->withDelimiter(Delimiter::fromString($command->delimiter))
            ->withTextTransformation(TextTransformation::fromString($command->textTransformation));

        $this->identifierGeneratorRepository->update($updatedIdentifierGenerator);
    }
}
