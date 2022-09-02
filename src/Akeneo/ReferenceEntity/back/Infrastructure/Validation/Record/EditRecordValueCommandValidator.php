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

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditRecordValueCommand;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Validation\Record\EditRecordValueCommand as EditRecordValueCommandConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditRecordValueCommandValidator extends ConstraintValidator
{
    public function __construct(
        private RecordExistsInterface $recordExists
    ) {
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
        if (!$command instanceof EditRecordValueCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given',
                    EditRecordValueCommand::class,
                    $command::class
                )
            );
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof EditRecordValueCommandConstraint) {
            throw new UnexpectedTypeException($constraint, EditRecordValueCommandConstraint::class);
        }
    }

    private function validateCommand(EditRecordValueCommand $command): void
    {
        if ('' === $command->recordCode || null === $command->recordCode) {
            return;
        }

        $this->context->getValidator()
            ->inContext($this->context)
            ->atPath((string) $command->attribute->getCode())
            ->validate($command->recordCode, new Code());

        if ($this->context->getViolations()->count() > 0) {
            return;
        }

        $recordsFound = $this->recordExists->withReferenceEntityAndCode(
            ReferenceEntityIdentifier::fromString($command->attribute->getRecordType()->normalize()),
            RecordCode::fromString($command->recordCode)
        );

        if (!$recordsFound) {
            $this->context->buildViolation(EditRecordValueCommandConstraint::ERROR_MESSAGE)
                ->atPath((string) $command->attribute->getCode())
                ->setParameter('%record_code%', $command->recordCode)
                ->addViolation();
        }
    }
}
