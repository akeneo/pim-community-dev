<?php

namespace Akeneo\Tool\Component\Batch\Job\JobParameters;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Symfony\Component\Validator\Constraints\Collection;

/**
 * Provides the minimal providers to validate a Job parameters
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EmptyConstraintAndDefaultValuesProvider implements
    ConstraintCollectionProviderInterface,
    DefaultValuesProviderInterface
{
    public function __construct(protected string $supportedJobName)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection(): Collection
    {
        return new Collection(['fields' => []]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return $job->getName() === $this->supportedJobName;
    }

    public function getDefaultValues(): array
    {
        return [];
    }
}
