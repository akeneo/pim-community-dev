<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Validator\Constraints;

use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Job\UndefinedJobException;
use Akeneo\Tool\Component\Batch\Model\JobInstance as JobInstanceModel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for job instance entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceValidator extends ConstraintValidator
{
    /** @var JobRegistry */
    protected $jobRegistry;

    /**
     * Constructor
     *
     * @param JobRegistry $jobRegistry
     */
    public function __construct(JobRegistry $jobRegistry)
    {
        $this->jobRegistry = $jobRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint)
    {
        if ($entity instanceof JobInstanceModel) {
            try {
                $this->jobRegistry->get($entity->getJobName());
            } catch (UndefinedJobException $e) {
                $this->context
                    ->buildViolation(
                        $constraint->message,
                        ['%job_type%' => $entity->getType()]
                    )
                    ->atPath($constraint->property)
                    ->addViolation();
            }
        }
    }
}
