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
    public const UNKNOWN_JOB_DEFINITION = 'akeneo_batch.job_instance.unknown_job_definition';
    public const SCHEDULED_SHOULD_BE_ENABLED = 'akeneo_batch.job_instance.scheduled_should_be_enabled';
    public const IMPORT_SHOULD_HAVE_STORAGE = 'akeneo_batch.job_instance.import_should_have_storage';

    public function __construct(
        private bool $isInScheduledContext = false,
    )
    {
        parent::__construct();
    }

    public function isInScheduledContext(): bool
    {
        return $this->isInScheduledContext;
    }

    public function validatedBy()
    {
        return 'akeneo_job_instance_validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
