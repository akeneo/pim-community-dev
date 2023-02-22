<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Job;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanCategoryEnrichedValuesProvider implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface
{
    public function __construct(
        private readonly string $jobName,
    ) {
    }

    public function supports(JobInterface $job): bool
    {
        return $this->jobName === $job->getName();
    }

    public function getDefaultValues(): array
    {
        return [
            'channel_code' => null,
            'locales_codes' => [],
            'action' => '',
        ];
    }

    /**
     * channel_code: deleted channel's code to be cleaned from category enriched values.
     */
    public function getConstraintCollection(): Collection
    {
        return new Collection(
            [
                'fields' => [
                    'channel_code' => new Type('string'),
                    'locales_codes' => new Type('array'),
                    'action' => new Type('string'),
                ],
            ],
        );
    }
}
