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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\Provider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Tool\Component\StorageUtils\Validator\Constraints\WritableDirectory;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;

final class TableValuesCsvExportProvider implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface
{
    private $supportedJobNames = ['csv_product_table_values_export', 'csv_product_model_table_values_export'];

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection(): Collection
    {
        return new Collection(
            [
                'fields' => [
                    'filePath'   => [
                        new NotBlank(['groups' => ['Execution', 'FileConfiguration']]),
                        new WritableDirectory(['groups' => ['Execution', 'FileConfiguration']]),
                        new Regex([
                            'pattern' => '/.\.csv$/',
                            'message' => 'The extension file must be ".csv"'
                        ])
                    ],
                    'delimiter'  => [
                        new NotBlank(['groups' => ['Default', 'FileConfiguration']]),
                        new Choice(
                            [
                                'strict' => true,
                                'choices' => [",", ";", "|"],
                                'message' => 'The value must be one of , or ; or |',
                                'groups'  => ['Default', 'FileConfiguration'],
                            ]
                        ),
                    ],
                    'enclosure'  => [
                        [
                            new NotBlank(['groups' => ['Default', 'FileConfiguration']]),
                            new Choice(
                                [
                                    'strict' => true,
                                    'choices' => ['"', "'"],
                                    'message' => 'The value must be one of " or \'',
                                    'groups'  => ['Default', 'FileConfiguration'],
                                ]
                            ),
                        ],
                    ],
                    'withHeader' => new Type(
                        [
                            'type'   => 'bool',
                            'groups' => ['Default', 'FileConfiguration'],
                        ]
                    ),
                    'user_to_notify' => new Type('string'),
                    'is_user_authenticated' => new Type('bool'),
                    'filters' => new Collection(
                        [
                            'fields'           => [
                                'table_attribute_code' => [
                                    new Type('string'),
                                    new NotBlank(),
                                ],
                            ],
                        ]
                    ),
                    'with_label' => new Type(
                        [
                            'type'   => 'bool',
                            'groups' => ['Default', 'FileConfiguration'],
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return \in_array($job->getName(), $this->supportedJobNames);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultValues(): array
    {
        return [
            'filePath' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'export_%job_label%_%datetime%.csv',
            'delimiter' => ';',
            'enclosure' => '"',
            'withHeader' => true,
            'user_to_notify' => null,
            'is_user_authenticated' => false,
            'filters' => [
                'table_attribute_code' => null,
            ],
            'with_label' => false,
        ];
    }
}
