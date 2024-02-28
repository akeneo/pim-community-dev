<?php

namespace Akeneo\Tool\Component\Batch\Job\JobParameters;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Symfony\Component\Validator\Constraints\Collection;

/**
 * Provides empty constraints that may be used to validate a JobParameters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EmptyConstraintCollectionProvider implements ConstraintCollectionProviderInterface
{
    public function __construct(protected array $supportedJobNames)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection(): Collection
    {
        return new Collection([]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
