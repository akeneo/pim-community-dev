<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\Provider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class ComputeCompletenessOfTableAttributeProducts implements DefaultValuesProviderInterface, ConstraintCollectionProviderInterface
{
    public function getConstraintCollection(): Collection
    {
        return new Collection(
            [
                'fields' => [
                    'attribute_code' => [
                        new Type('string'),
                        new NotBlank(),
                    ],
                    'family_codes' => [
                        new Type('array'),
                        new All([new Type('string'), new NotBlank()]),
                    ],
                ],
            ]
        );
    }

    public function getDefaultValues(): array
    {
        return [
            'attribute_code' => null,
            'family_codes' => [],
        ];
    }

    public function supports(JobInterface $job): bool
    {
        return 'compute_completeness_following_table_update' === $job->getName();
    }
}
