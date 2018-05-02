<?php

namespace Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderRegistry;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    /** @var ConstraintCollectionProviderRegistry */
    protected $registry;

    /**
     * @param ValidatorInterface                   $validator
     * @param ConstraintCollectionProviderRegistry $registry
     */
    public function __construct(ValidatorInterface $validator, ConstraintCollectionProviderRegistry $registry)
    {
        $this->validator = $validator;
        $this->registry = $registry;
    }

    /**
     * @param JobInterface  $job
     * @param JobParameters $jobParameters
     * @param array         $groups
     *
     * @return ConstraintViolationListInterface A list of constraint violations. If the
     *                                          list is empty, validation succeeded.
     */
    public function validate(JobInterface $job, JobParameters $jobParameters, $groups = null)
    {
        $provider = $this->registry->get($job);
        $collection = $provider->getConstraintCollection();
        $parameters = $jobParameters->all();
        $errors = $this->validator->validate($parameters, $collection, $groups);

        return $errors;
    }
}
