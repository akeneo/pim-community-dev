<?php

namespace Akeneo\Tool\Component\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\Storage;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Type;

/**
 * Constraints for simple Yaml export
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleYamlExport implements ConstraintCollectionProviderInterface
{
    /**
     * @param array<string> $supportedJobNames
     */
    public function __construct(
        private array $supportedJobNames,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection(): Collection
    {
        return new Collection(
            [
                'fields' => [
                    'storage'   => new Storage(['yml', 'yaml']),
                    'users_to_notify' => [
                        new Type('array'),
                        new All(new Type('string')),
                    ],
                    'is_user_authenticated' => new Type('bool'),
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
