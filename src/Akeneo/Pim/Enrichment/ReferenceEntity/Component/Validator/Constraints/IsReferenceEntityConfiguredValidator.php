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

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Validator\Constraints;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityDetailsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Checks if the reference entity is well configured for attribute entity.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IsReferenceEntityConfiguredValidator extends ConstraintValidator
{
    /** @var array */
    protected $referenceEntityTypes;

    /** @var FindReferenceEntityDetailsInterface */
    protected $findReferenceEntityDetails;

    /**
     * @param array $referenceEntityTypes
     * @param FindReferenceEntityDetailsInterface $findReferenceEntityDetails
     */
    public function __construct(
        array $referenceEntityTypes,
        FindReferenceEntityDetailsInterface $findReferenceEntityDetails
    ) {
        $this->referenceEntityTypes = $referenceEntityTypes;
        $this->findReferenceEntityDetails = $findReferenceEntityDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($attribute, Constraint $constraint)
    {
        $rawReferenceEntityIdentifier = $attribute->getReferenceDataName();

        if (null === $rawReferenceEntityIdentifier || '' === $rawReferenceEntityIdentifier) {
            $this->addEmptyViolation($this->context, $constraint);

            return;
        }

        try {
            $referenceEntityIdentifier = ReferenceEntityIdentifier::fromString($rawReferenceEntityIdentifier);
        } catch (\InvalidArgumentException $e) {
            $this->addInvalidViolation($constraint, $rawReferenceEntityIdentifier);

            return;
        }

        if (in_array($attribute->getType(), $this->referenceEntityTypes) &&
            null === ($this->findReferenceEntityDetails)($referenceEntityIdentifier)
        ) {
            $this->addUnknownViolation($constraint, $rawReferenceEntityIdentifier);
        }
    }

    private function addEmptyViolation(ExecutionContextInterface $context, Constraint $constraint)
    {
        $context
            ->buildViolation($constraint->emptyMessage)
            ->atPath($constraint->propertyPath)
            ->addViolation();
    }

    private function addInvalidViolation(Constraint $constraint, string $rawReferenceEntityIdentifier)
    {
        $this->context
            ->buildViolation($constraint->invalidMessage)
            ->setParameter('%reference_entity_identifier%', $rawReferenceEntityIdentifier)
            ->atPath($constraint->propertyPath)
            ->addViolation();
    }

    private function addUnknownViolation(Constraint $constraint, string $rawReferenceEntityIdentifier)
    {
        $this->context
            ->buildViolation($constraint->unknownMessage)
            ->setParameter('%reference_entity_identifier%', $rawReferenceEntityIdentifier)
            ->atPath($constraint->propertyPath)
            ->addViolation();
    }
}
