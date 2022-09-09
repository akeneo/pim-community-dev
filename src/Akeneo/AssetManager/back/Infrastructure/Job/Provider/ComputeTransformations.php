<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Job\Provider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class ComputeTransformations implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface
{
    public function getConstraintCollection(): Collection
    {
        return new Collection(
            [
                'fields' => [
                    'asset_identifiers' => new All(
                        [
                            new Type(['type' => 'string']),
                            new NotBlank(),
                        ]
                    ),
                    'asset_family_identifier' => new Type(['type' => 'string'])
                ],
                'allowMissingFields' => true,
            ]
        );
    }

    public function supports(JobInterface $job): bool
    {
        return 'asset_manager_compute_transformations' === $job->getName();
    }

    public function getDefaultValues(): array
    {
        return [
            'asset_identifiers' => [],
        ];
    }
}
