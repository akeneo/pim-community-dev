<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Validation\Record;

use Akeneo\EnrichedEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\ExistsRecordInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Checks whether a given record already exists in the data referential
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RecordIdentifierShouldBeUniqueValidator extends ConstraintValidator
{
    /** @var ExistsRecordInterface */
    private $existsRecord;

    public function __construct(ExistsRecordInterface $existsRecord)
    {
        $this->existsRecord = $existsRecord;
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
        if (!$command instanceof CreateRecordCommand) {
            throw new \InvalidArgumentException(sprintf('Expected argument to be of class "%s", "%s" given',
                CreateRecordCommand::class, get_class($command)));
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof RecordIdentifierShouldBeUnique) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    private function validateCommand(CreateRecordCommand $command): void
    {
        $enrichedEntityIdentifier = $command->identifier['enriched_entity_identifier'];
        $identifier = $command->identifier['identifier'];
        $alreadyExists = $this->existsRecord->withIdentifier(
            RecordIdentifier::create(
                $enrichedEntityIdentifier,
                $identifier
            )
        );
        if ($alreadyExists) {
            $this->context->buildViolation(RecordIdentifierShouldBeUnique::ERROR_MESSAGE)
                ->setParameter('%enriched_entity_identifier%', $enrichedEntityIdentifier)
                ->setParameter('%code%', $identifier)
                ->atPath('identifier')
                ->addViolation();
        }
    }
}
