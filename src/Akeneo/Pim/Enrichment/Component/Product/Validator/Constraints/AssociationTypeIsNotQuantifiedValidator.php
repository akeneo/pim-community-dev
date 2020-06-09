<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AssociationTypeIsNotQuantifiedValidator extends ConstraintValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof AssociationInterface) {
            throw new UnexpectedTypeException($value, AssociationInterface::class);
        }

        if (!$constraint instanceof AssociationTypeIsNotQuantified) {
            throw new UnexpectedTypeException($constraint, AssociationTypeIsNotQuantified::class);
        }

        if ($value->getAssociationType()->isQuantified()) {
            $this->context
                ->buildViolation(
                    AssociationTypeIsNotQuantified::ASSOCIATION_TYPE_SHOULD_NOT_BE_QUANTIFIED_MESSAGE,
                    [
                        '{{ association_type }}' => $value->getAssociationType()->getCode(),
                    ]
                )
                ->addViolation();
        }
    }
}
