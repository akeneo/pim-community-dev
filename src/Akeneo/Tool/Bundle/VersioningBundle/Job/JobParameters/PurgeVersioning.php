<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Job\JobParameters;

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
class PurgeVersioning implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface
{
    private const JOB_NAME = 'versioning_purge';

    public function supports(JobInterface $job): bool
    {
        return self::JOB_NAME === $job->getName();
    }

    public function getDefaultValues(): array
    {
        return [
            'entity' => null,
            'more-than-days' => null,
            'less-than-days' => null,
            'batch-size' => 100,
        ];
    }

    /**
     * entity: Fully qualified classname of the entity to purge. (beware of overridden or custom entities)
     * more-than-days: Purges the versions that are older than the number of days
     * less-than-days: Purges the versions that are younger than the number of days'
     * batch-size: Purges the versions by batch
     */
    public function getConstraintCollection(): Collection
    {
        return new Collection(
            [
                'fields' => [
                    'entity' => new Type('string'),
                    'more-than-days' => new Type('int'),
                    'less-than-days' => new Type('int'),
                    'batch-size' => new Type('int'),
                ],
            ]
        );
    }
}
