<?php

namespace Oro\Bundle\QueryDesignerBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class FilterLogicConstraint extends  Constraint
{
    const FILTER_LOGIC_VALIDATOR_CLASS = 'Oro\Bundle\QueryDesignerBundle\Validator\FilterLogicValidator';

    /**
     * @inheritdoc
     */
    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }

    /**
     * @inheritdoc
     */
    public function validatedBy()
    {
        return self::FILTER_LOGIC_VALIDATOR_CLASS;
    }
}
