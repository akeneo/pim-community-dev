<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class AssociationTypeIsNotQuantified extends Constraint
{
    public const ASSOCIATION_TYPE_SHOULD_NOT_BE_QUANTIFIED_MESSAGE = 'pim_catalog.constraint.quantified_associations.association_type_should_not_be_quantified';

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string|array
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
