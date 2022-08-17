<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints;

use Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints\JobInstance as JobInstanceConstraint;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Job\UndefinedJobException;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validator for job instance entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceValidator extends ConstraintValidator
{
    public function __construct(
        private JobRegistry $jobRegistry
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof JobInstanceConstraint) {
            throw new UnexpectedTypeException($constraint, JobInstanceConstraint::class);
        }

        if ($value instanceof JobInstance) {
            try {
                $this->jobRegistry->get($value->getJobName());
            } catch (UndefinedJobException $e) {
                $this->context
                    ->buildViolation(
                        JobInstanceConstraint::UNKNOWN_JOB_DEFINITION,
                        ['%job_type%' => $value->getType()]
                    )
                    ->atPath('jobName')
                    ->addViolation();
            }
        }

        if($constraint->isInScheduledContext() && !$value->isScheduled()) {
            $this->context->buildViolation(JobInstanceConstraint::SCHEDULED_SHOULD_BE_ENABLED)->addViolation();
        }
    }
}
