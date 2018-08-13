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

namespace Akeneo\Pim\EnrichedEntity\Component\Validator\Constraints;

use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity\FindEnrichedEntityDetailsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Checks if the enriched entity is well configured for attribute entity.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IsEnrichedEntityConfiguredValidator extends ConstraintValidator
{
    /** @var array */
    protected $enrichedEntityTypes;

    /** @var FindEnrichedEntityDetailsInterface */
    protected $findEnrichedEntityDetails;

    /**
     * @param array                              $enrichedEntityTypes
     * @param FindEnrichedEntityDetailsInterface $findEnrichedEntityDetails
     */
    public function __construct(array $enrichedEntityTypes, FindEnrichedEntityDetailsInterface $findEnrichedEntityDetails)
    {
        $this->enrichedEntityTypes       = $enrichedEntityTypes;
        $this->findEnrichedEntityDetails = $findEnrichedEntityDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($attribute, Constraint $constraint)
    {
        $rawEnrichedEntityIdentifier = $attribute->getReferenceDataName();

        if (null === $rawEnrichedEntityIdentifier || '' === $rawEnrichedEntityIdentifier) {
            $this->addEmptyViolation($this->context, $constraint);

            return;
        }

        try {
            $enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString($rawEnrichedEntityIdentifier);
        } catch (\InvalidArgumentException $e) {
            $this->addInvalidViolation($this->context, $constraint, $rawEnrichedEntityIdentifier);

            return;
        }

        if (in_array($attribute->getType(), $this->enrichedEntityTypes) &&
            null === ($this->findEnrichedEntityDetails)($enrichedEntityIdentifier)
        ) {
            $this->addUnknownViolation($this->context, $constraint, $rawEnrichedEntityIdentifier);
        }
    }

    private function addEmptyViolation(ExecutionContextInterface $context, Constraint $constraint)
    {
        $context
            ->buildViolation($constraint->emptyMessage)
            ->atPath($constraint->propertyPath)
            ->addViolation();
    }

    private function addInvalidViolation(
        ExecutionContextInterface $context,
        Constraint $constraint,
        string $rawEnrichedEntityIdentifier
    ) {
        $this->context
            ->buildViolation($constraint->invalidMessage)
            ->setParameter('%enriched_entity_identifier%', $rawEnrichedEntityIdentifier)
            ->atPath($constraint->propertyPath)
            ->addViolation();
    }

    private function addUnknownViolation(
        ExecutionContextInterface $context,
        Constraint $constraint,
        string $rawEnrichedEntityIdentifier
    ) {
        $this->context
            ->buildViolation($constraint->unknownMessage)
            ->setParameter('%enriched_entity_identifier%', $rawEnrichedEntityIdentifier)
            ->atPath($constraint->propertyPath)
            ->addViolation();
    }
}
