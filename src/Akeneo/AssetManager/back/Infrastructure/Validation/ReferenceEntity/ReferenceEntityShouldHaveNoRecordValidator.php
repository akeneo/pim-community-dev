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

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\ReferenceEntity;

use Akeneo\ReferenceEntity\Application\ReferenceEntity\DeleteReferenceEntity\DeleteReferenceEntityCommand;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityHasRecordsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class ReferenceEntityShouldHaveNoRecordValidator extends ConstraintValidator
{
    /** @var ReferenceEntityHasRecordsInterface */
    private $referenceEntityHasRecords;

    public function __construct(ReferenceEntityHasRecordsInterface $referenceEntityHasRecords)
    {
        $this->referenceEntityHasRecords = $referenceEntityHasRecords;
    }

    public function validate($command, Constraint $constraint): void
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
        if (!$command instanceof DeleteReferenceEntityCommand) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected argument to be of class "%s", "%s" given',
                    DeleteReferenceEntityCommand::class,
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
        if (!$constraint instanceof ReferenceEntityShouldHaveNoRecord) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    private function validateCommand(DeleteReferenceEntityCommand $command): void
    {
        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($command->identifier);
        $hasRecords = $this->referenceEntityHasRecords->hasRecords($referenceEntityIdentifier);

        if ($hasRecords) {
            $this->context->buildViolation(ReferenceEntityShouldHaveNoRecord::ERROR_MESSAGE)
                ->setParameter('%reference_entity_identifier%', $referenceEntityIdentifier)
                ->addViolation();
        }
    }
}
