<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Component\Validator;

use Symfony\Component\Validator\Constraint;

class GrantedQuantifiedAssociations extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pimee_granted_quantified_associations';
    }
}
