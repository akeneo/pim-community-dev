<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Job instance validator
 * Validate connector and alias for a job instance
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
    public $message = 'Failed to create an {{ job_type }} with an unknown job definition';

    /**
     * @var string
     */
    public $property = 'alias';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_job_instance_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
