<?php

namespace Akeneo\Component\Batch\Job;

use Akeneo\Component\Batch\Job\JobParameters\ConstraintsRegistry;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Validate a JobParameters depending on the Job we're editing or launching
 *
 * This implementation rely on the ConstraintsRegistry to fetch the relevant Constraints to apply.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobParametersValidator
{
    /** @var ValidatorInterface */
    protected $validator;

    /** @var ConstraintsRegistry */
    protected $registry;

    /**
     * @param ValidatorInterface  $validator
     * @param ConstraintsRegistry $registry
     */
    public function __construct(ValidatorInterface $validator, ConstraintsRegistry $registry)
    {
        $this->validator = $validator;
        $this->registry = $registry;
    }

    /**
     * @param Job           $job
     * @param JobParameters $jobParameters
     * @param array         $groups
     *
     * @return ConstraintViolationListInterface A list of constraint violations. If the
     *                                          list is empty, validation succeeded.
     */
    public function validate(Job $job, JobParameters $jobParameters, $groups = null)
    {
        $constraints = $this->registry->getConstraints($job)->getConstraints();
        $parameters = $jobParameters->getParameters();
        $errors = $this->validator->validateValue($parameters, $constraints, $groups);

        return $errors;
    }
}
