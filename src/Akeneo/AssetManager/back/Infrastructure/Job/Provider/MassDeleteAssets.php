<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Job\Provider;

use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class MassDeleteAssets implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface
{
    public function getConstraintCollection(): Collection
    {
        return new Collection(
            [
                'fields' => [
                    'query' => new Callback(function ($value, ExecutionContextInterface $context) {
                        try {
                            AssetQuery::createFromNormalized($value);
                        } catch (\InvalidArgumentException $e) {
                            $context
                                ->buildViolation($e->getMessage())
                                ->addViolation();
                        }
                    }),
                    'asset_family_identifier' => new Type(['type' => 'string']),
                    'users_to_notify' => [
                        new Type('array'),
                        new All([new Type('string')]),
                    ],
                ],
                'allowMissingFields' => false,
            ]
        );
    }

    public function supports(JobInterface $job): bool
    {
        return 'asset_manager_mass_delete_assets' === $job->getName();
    }

    public function getDefaultValues(): array
    {
        return [];
    }
}
