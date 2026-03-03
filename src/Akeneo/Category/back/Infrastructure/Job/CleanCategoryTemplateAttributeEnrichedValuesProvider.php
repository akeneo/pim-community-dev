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
class CleanCategoryTemplateAttributeEnrichedValuesProvider implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface
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
            'template_uuid' => null,
            'attribute_uuid' => null,
        ];
    }

    /**
     * template_uuid: deleted template's attribute uuid to be cleaned from category enriched values.
     * attribute_uuid: deleted attribute's uuid to be cleaned from category enriched values.
     */
    public function getConstraintCollection(): Collection
    {
        return new Collection(
            [
                'fields' => [
                    'template_uuid' => new Type('string'),
                    'attribute_uuid' => new Type('string'),
                ],
            ],
        );
    }
}
