<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Job\Provider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 */
class ExecuteNamingConvention implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface
{
    public function getConstraintCollection(): Collection
    {
        return new Collection([
            'fields' => [
                'asset_family_identifier' => [
                    new Type('string'),
                    new NotBlank(),
                ],
            ],
        ]);
    }

    public function supports(JobInterface $job): bool
    {
        return 'asset_manager_execute_naming_convention' === $job->getName();
    }

    public function getDefaultValues(): array
    {
        return [
            'asset_family_identifier' => null,
        ];
    }
}
