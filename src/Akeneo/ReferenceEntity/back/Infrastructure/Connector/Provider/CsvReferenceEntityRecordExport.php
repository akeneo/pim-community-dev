<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Provider;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\NoneStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\Storage;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class CsvReferenceEntityRecordExport implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultValues(): array
    {
        return [
            'storage' => [
                'type' => NoneStorage::TYPE,
                'file_path' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'export_%job_label%_%datetime%.csv'
            ],
            'delimiter' => ';',
            'enclosure' => '"',
            'withHeader' => true,
            'with_media' => true,
            'users_to_notify' => [],
            'is_user_authenticated' => false,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection(): Collection
    {
        return new Collection(
            [
                'fields' => [
                    'storage' => new Storage(['csv']),
                    'delimiter' => [
                        new NotBlank(['groups' => ['Default', 'FileConfiguration']]),
                        new Choice(
                            [
                                'strict' => true,
                                'choices' => [",", ";", "|"],
                                'message' => 'The value must be one of , or ; or |',
                                'groups' => ['Default', 'FileConfiguration'],
                            ]
                        ),
                    ],
                    'enclosure' => [
                        [
                            new NotBlank(['groups' => ['Default', 'FileConfiguration']]),
                            new Choice(
                                [
                                    'strict' => true,
                                    'choices' => ['"', "'"],
                                    'message' => 'The value must be one of " or \'',
                                    'groups' => ['Default', 'FileConfiguration'],
                                ]
                            ),
                        ],
                    ],
                    'withHeader' => new Type(
                        [
                            'type' => 'bool',
                            'groups' => ['Default', 'FileConfiguration'],
                        ]
                    ),
                    'with_media' => new Type('bool'),
                    'users_to_notify' => [
                        new Type('array'),
                        new All([new Type('string')]),
                    ],
                    'is_user_authenticated' => new Type('bool'),
                    'reference_entity_identifier' => [
                        new Type('string'),
                        new NotBlank(),
                    ],
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return 'csv_reference_entity_record_export' === $job->getName();
    }
}
