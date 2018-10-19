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
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordExistsInterface;
use Akeneo\ReferenceEntity\Infrastructure\Validation\Record\EditRecordCollectionValueCommand as EditRecordCollectionValueCommandConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditRecordCollectionValueCommandValidator extends ConstraintValidator
{
    /** @var RecordExistsInterface */
    private $recordExists;

    public function __construct(RecordExistsInterface $recordExists)
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
        $recordsFound = $this->recordExists->withReferenceEntityAndCodes(
            ReferenceEntityIdentifier::fromString($command->attribute->getRecordType()->normalize()),
            $command->recordCodes
        );

        $recordsNotExist = array_diff($command->recordCodes, $recordsFound);

        if (!empty($recordsNotExist)) {
            $this->context->buildViolation(EditRecordCollectionValueCommandConstraint::ERROR_MESSAGE)
                ->atPath((string) $command->attribute->getCode())
                ->setParameter('%record_codes%', implode(',', $recordsNotExist))
                ->addViolation();
        }
    }
}
