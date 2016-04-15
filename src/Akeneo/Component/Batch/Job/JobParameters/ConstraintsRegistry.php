<?php

namespace Akeneo\Component\Batch\Job\JobParameters;

use Akeneo\Component\Batch\Job\JobInterface;

/**
 * Registry of constraints that can be used to validate a JobParameters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConstraintsRegistry
{
    /** @var ConstraintsInterface[] */
    protected $constraints = [];

    /**
     * @param ConstraintsInterface $constraint
     */
    public function register(ConstraintsInterface $constraint)
    {
        $this->constraints[] = $constraint;
    }

    /**
     * @param JobInterface $job
     *
     * @return ConstraintsInterface
     */
    public function getConstraints(JobInterface $job)
    {
        foreach ($this->constraints as $constraint) {
            if ($constraint->supports($job)) {
                return $constraint;
            }
        }

        // TODO: not a good idea? Should we raise an Exception to force to always declare Contraints for a Job?
        return new EmptyConstraints();
    }
}
