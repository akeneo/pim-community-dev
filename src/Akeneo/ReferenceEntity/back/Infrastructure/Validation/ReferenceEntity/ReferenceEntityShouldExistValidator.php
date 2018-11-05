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

use Akeneo\ReferenceEntity\Application\Record\IndexRecords\IndexRecordsByReferenceEntityCommand;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Checks whether a given reference_entity already exists in the data referential
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceEntityShouldExistValidator extends ConstraintValidator
{
    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    public function __construct(ReferenceEntityExistsInterface $recordExists)
    {
        $this->referenceEntityExists = $recordExists;
    }

    public function validate($command, Constraint $constraint): void
    {
        $this->checkConstraintType($constraint);
        $this->validateCommand($command);
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof ReferenceEntityShouldExist) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    private function validateCommand(IndexRecordsByReferenceEntityCommand $command): void
    {
        $referenceEntityIdentifier = $command->referenceEntityIdentifier;
        $alreadyExists = $this->referenceEntityExists->withIdentifier(ReferenceEntityIdentifier::fromString($referenceEntityIdentifier));
        if (!$alreadyExists) {
            $this->context->buildViolation(ReferenceEntityShouldExist::ERROR_MESSAGE)
                ->setParameter('%reference_entity_code%', $referenceEntityIdentifier)
                ->atPath('code')
                ->addViolation();
        }
    }
}
