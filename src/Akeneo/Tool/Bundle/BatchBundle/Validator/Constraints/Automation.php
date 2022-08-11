<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class Automation extends Constraint
{
    public function validatedBy()
    {
        return 'akeneo_batch.validator.job_instance.automation';
    }
}
