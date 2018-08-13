<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\Infrastructure\Validation\EnrichedEntity;

use Akeneo\EnrichedEntity\Application\EnrichedEntity\CreateEnrichedEntity\CreateEnrichedEntityCommand;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntityExistsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Checks whether a given record already exists in the data referential
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EnrichedEntityIdentifierShouldBeUniqueValidator extends ConstraintValidator
{
    /** @var EnrichedEntityExistsInterface */
    private $recordExists;

    public function __construct(EnrichedEntityExistsInterface $recordExists)
    {
        $this->recordExists = $recordExists;
    }

    public function validate($command, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);
        $this->checkCommandType($command);
        $this->validateCommand($command);
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkCommandType($command): void
    {
        if (!$command instanceof CreateEnrichedEntityCommand) {
            throw new \InvalidArgumentException(sprintf('Expected argument to be of class "%s", "%s" given',
                CreateEnrichedEntityCommand::class, get_class($command)));
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof EnrichedEntityIdentifierShouldBeUnique) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    private function validateCommand(CreateEnrichedEntityCommand $command): void
    {
        $enrichedEntityIdentifier = $command->identifier;
        $alreadyExists = $this->recordExists->withIdentifier(
            EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier)
        );
        if ($alreadyExists) {
            $this->context->buildViolation(EnrichedEntityIdentifierShouldBeUnique::ERROR_MESSAGE)
                ->setParameter('%enriched_entity_identifier%', $enrichedEntityIdentifier)
                ->atPath('identifier')
                ->addViolation();
        }
    }
}
