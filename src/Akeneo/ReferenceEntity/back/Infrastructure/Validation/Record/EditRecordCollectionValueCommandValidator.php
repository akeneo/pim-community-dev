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

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Record;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordCollectionValueCommand;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindExistingRecordCodesInterface;
use Akeneo\ReferenceEntity\Infrastructure\Validation\Record\EditRecordCollectionValueCommand as EditRecordCollectionValueCommandConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditRecordCollectionValueCommandValidator extends ConstraintValidator
{
    /** @var FindExistingRecordCodesInterface */
    private $existingRecordCodes;

    public function __construct(FindExistingRecordCodesInterface $existingRecordCodes)
    {
        $this->existingRecordCodes = $existingRecordCodes;
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
        if (!$command instanceof EditRecordCollectionValueCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given', EditRecordCollectionValueCommand::class,
                    get_class($command)
                )
            );
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof EditRecordCollectionValueCommandConstraint) {
            throw new UnexpectedTypeException($constraint, EditRecordCollectionValueCommandConstraint::class);
        }
    }

    private function validateCommand(EditRecordCollectionValueCommand $command): void
    {
        $recordCodes = array_filter(
            $command->recordCodes,
            fn (?string $code): bool => null !== $code && '' !== $code
        );
        if (0 === count($recordCodes)) {
            return;
        }

        $foundRecords = $this->existingRecordCodes->find(
            ReferenceEntityIdentifier::fromString($command->attribute->getRecordType()->normalize()),
            $recordCodes
        );

        $missingRecords = array_diff($recordCodes, $foundRecords);

        if (!empty($missingRecords)) {
            $this->context->buildViolation(EditRecordCollectionValueCommandConstraint::ERROR_MESSAGE)
                ->atPath((string) $command->attribute->getCode())
                ->setParameter('%record_codes%', implode(',', $missingRecords))
                ->addViolation();
        }
    }
}
