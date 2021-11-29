<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\back\Infrastructure\Connector\Provider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class CleanTablesValuesOnDeletedOptions implements DefaultValuesProviderInterface, ConstraintCollectionProviderInterface
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
                    'removed_options_per_column_code' => [
                        new Type('array'),
                        new All([new Type('array'), new NotBlank(), new All([new Type('string'), new NotBlank()])]),
                    ],
                ],
            ]
        );
    }

    public function getDefaultValues(): array
    {
        return [
            'attribute_code' => null,
            'removed_options_per_column_code' => [],
        ];
    }

    public function supports(JobInterface $job): bool
    {
        return 'clean_table_values_following_deleted_options' === $job->getName();
    }
}
