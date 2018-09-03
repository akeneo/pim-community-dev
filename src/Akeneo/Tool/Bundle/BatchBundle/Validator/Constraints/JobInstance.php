<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Job instance validator
 * Validate connector and job name for a job instance
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstance extends Constraint
{
    /**
     * @var string
     */
    public $message = 'akeneo_batch.job_instance.unknown_job_definition';

    /**
     * @var string
     */
    public $property = 'jobName';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'akeneo_job_instance_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
