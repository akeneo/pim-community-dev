<?php

namespace Akeneo\Tool\Component\Batch\Job\JobParameters;

use Akeneo\Tool\Component\Batch\Job\JobInterface;

/**
 * Provides empty default values to setup a JobParameters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EmptyDefaultValuesProvider implements DefaultValuesProviderInterface
{
    public function __construct(protected array $supportedJobNames)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValues(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
