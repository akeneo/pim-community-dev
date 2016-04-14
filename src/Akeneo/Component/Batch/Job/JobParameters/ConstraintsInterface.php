<?php

namespace Akeneo\Component\Batch\Job\JobParameters;

use Akeneo\Component\Batch\Job\JobInterface;
use Symfony\Component\Validator\Constraints\Collection;

/**
 * Constraints to be used to validate a JobParameters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ConstraintsInterface
{
    /**
     * @return Collection
     */
    public function getConstraints();

    /**
     * @return boolean
     */
    public function supports(JobInterface $job);
}
