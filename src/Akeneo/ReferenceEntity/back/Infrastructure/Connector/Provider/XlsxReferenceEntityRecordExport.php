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

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Provider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Tool\Component\StorageUtils\Validator\Constraints\WritableDirectory;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;

class XlsxReferenceEntityRecordExport implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultValues()
    {
        return [
            'filePath' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'export_%job_label%_%datetime%.xlsx',
            'linesPerFile' => 10000,
            'withHeader' => true,
            'with_media' => true,
            'user_to_notify' => null,
            'is_user_authenticated' => false,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection()
    {
        return new Collection(
            [
                'fields' => [
                    'filePath' => [
                        new NotBlank(['groups' => ['Execution', 'FileConfiguration']]),
                        new WritableDirectory(['groups' => ['Execution', 'FileConfiguration']]),
                        new Regex(
                            [
                                'pattern' => '/.\.xlsx$/',
                                'message' => 'The extension file must be ".xlsx"'
                            ]
                        )
                    ],
                    'linesPerFile' => [
                        new NotBlank(['groups' => ['Default', 'FileConfiguration']]),
                        new GreaterThan(
                            [
                                'value' => 1,
                                'groups' => ['Default', 'FileConfiguration'],
                            ]
                        ),
                    ],
                    'withHeader' => new Type(
                        [
                            'type' => 'bool',
                            'groups' => ['Default', 'FileConfiguration'],
                        ]
                    ),
                    'with_media' => new Type('bool'),
                    'user_to_notify' => new Type('string'),
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
    public function supports(JobInterface $job)
    {
        return 'xlsx_reference_entity_record_export' === $job->getName();
    }
}
