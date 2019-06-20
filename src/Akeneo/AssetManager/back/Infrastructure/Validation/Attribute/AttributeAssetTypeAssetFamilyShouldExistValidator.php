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

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Attribute;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AttributeRecordTypeReferenceEntityShouldExistValidator extends ConstraintValidator
{
    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    public function __construct(ReferenceEntityExistsInterface $referenceEntityExists)
    {
        $this->referenceEntityExists = $referenceEntityExists;
    }

    public function validate($command, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);
        $this->checkCommandType($command);

        $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($command->recordType);
        if (false === $this->referenceEntityExists->withIdentifier($referenceEntityIdentifier)) {
            $this->context->buildViolation(AttributeRecordTypeReferenceEntityShouldExist::ERROR_MESSAGE)
                ->atPath('reference_entity_code')
                ->setParameter('%reference_entity_code%', $command->recordType)
                ->addViolation();
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkCommandType($command): void
    {
        if (!$command instanceof AbstractCreateAttributeCommand) {
            throw new \InvalidArgumentException(sprintf('Expected argument to be of class "%s", "%s" given',
                AbstractCreateAttributeCommand::class, get_class($command)));
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof AttributeRecordTypeReferenceEntityShouldExist) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }
}
