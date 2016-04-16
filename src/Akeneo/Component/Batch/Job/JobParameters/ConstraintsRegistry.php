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

    /** @var boolean */
    protected $isStrict;

    /**
     * @param ConstraintsInterface $constraint
     * @param boolean              $isStrict
     */
    public function register(ConstraintsInterface $constraint, $isStrict = true)
    {
        $this->constraints[] = $constraint;
        $this->isStrict = $isStrict;
    }

    /**
     * @param JobInterface $job
     *
     * @return ConstraintsInterface
     *
     * @throws UndefinedConstraintsException
     */
    public function getConstraints(JobInterface $job)
    {
        foreach ($this->constraints as $constraint) {
            if ($constraint->supports($job)) {
                return $constraint;
            }
        }

        if ($this->isStrict) {
            throw new UndefinedConstraintsException(
                sprintf('No constraints have been defined for the Job "%s"', $job->getName())
            );
        }

        return new EmptyConstraints();
    }
}
