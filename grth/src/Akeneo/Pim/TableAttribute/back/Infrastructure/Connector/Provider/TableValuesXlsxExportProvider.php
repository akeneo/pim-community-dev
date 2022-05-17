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

use Akeneo\Channel\Infrastructure\Component\Validator\Constraint\ActivatedLocale;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Tool\Component\StorageUtils\Validator\Constraints\WritableDirectory;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class TableValuesXlsxExportProvider implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface
{
    private $supportedJobNames = ['xlsx_product_table_values_export', 'xlsx_product_model_table_values_export'];

    /**
     * {@inheritDoc}
     */
    public function getConstraintCollection(): Collection
    {
        return new Collection(
            [
                'fields' => [
                    'filePath'     => [
                        new NotBlank(['groups' => ['Execution', 'FileConfiguration']]),
                        new WritableDirectory(['groups' => ['Execution', 'FileConfiguration']]),
                        new Regex([
                            'pattern' => '/.\.xlsx$/',
                            'message' => 'The extension file must be ".xlsx"'
                        ])
                    ],
                    'withHeader'   => new Type(
                        [
                            'type'   => 'bool',
                            'groups' => ['Default', 'FileConfiguration'],
                        ]
                    ),
                    'linesPerFile' => [
                        new NotBlank(['groups' => ['Default', 'FileConfiguration']]),
                        new GreaterThan(
                            [
                                'value'  => 1,
                                'groups' => ['Default', 'FileConfiguration'],
                            ]
                        ),
                    ],
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
                    'header_with_label' => new Type([
                        'type' => 'bool',
                        'groups' => ['Default', 'FileConfiguration'],
                    ]),
                    'file_locale' => [
                        new ActivatedLocale(['groups' => ['Default', 'FileConfiguration']]),
                        new Callback(function ($value, ExecutionContextInterface $context) {
                            $fields = $context->getRoot();
                            if (true === $fields['with_label'] && empty($value)) {
                                $context
                                    ->buildViolation('The locale cannot be empty.')
                                    ->addViolation();
                            }
                        })
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
        return \in_array($job->getName(), $this->supportedJobNames);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValues(): array
    {
        return [
            'filePath' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'export_%job_label%_%datetime%.xlsx',
            'withHeader' => true,
            'linesPerFile' => 10000,
            'user_to_notify' => null,
            'is_user_authenticated' => false,
            'filters' => [
                'table_attribute_code' => null,
            ],
            'with_label' => false,
            'header_with_label' => false,
            'file_locale' => null,
        ];
    }
}
