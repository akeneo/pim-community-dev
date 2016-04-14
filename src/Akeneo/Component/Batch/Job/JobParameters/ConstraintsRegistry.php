<?php

namespace Akeneo\Component\Batch\Job\JobParameters;

use Akeneo\Component\Batch\Job\JobInterface;

/**
 * Provides constraints used to validate a JobParameters
 * For instance, define that a filepath parameter should not be blank
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

        return new EmptyConstraints();
    }
}
