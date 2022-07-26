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
class RefreshVersioning implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface
{
    public function __construct(
        protected array $supportedJobNames
    ) {
    }

    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }

    public function getDefaultValues(): array
    {
        return [
            'batch_size' => 100,
        ];
    }

    public function getConstraintCollection(): Collection
    {
        return new Collection(
            [
                'fields' => [
                    'batch_size' => new Type('int'),
                ],
            ]
        );
    }
}
