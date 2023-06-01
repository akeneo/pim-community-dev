<?php

namespace Akeneo\Platform\Bundle\ImportExportBundle\Job\JobParameters;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeJobExecutions implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface
{
    private const JOB_NAME = 'job_executions_purge';

    public function supports(JobInterface $job): bool
    {
        return self::JOB_NAME === $job->getName();
    }

    public function getDefaultValues(): array
    {
        return [
            'days' => 90,
            'status' => null
        ];
    }

    /**
     * days: Purges the job executions that are older than the number of days.
     */
    public function getConstraintCollection(): Collection
    {
        return new Collection(
            [
                'fields' => [
                    'days' => new Type('int'),
                    'status' => new Type('int')
                ],
            ]
        );
    }
}
