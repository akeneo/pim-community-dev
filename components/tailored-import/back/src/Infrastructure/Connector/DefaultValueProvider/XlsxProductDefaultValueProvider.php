<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Connector\DefaultValueProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;

class XlsxProductDefaultValueProvider implements DefaultValuesProviderInterface
{
    public function __construct(
        private DefaultValuesProviderInterface $simpleProvider,
        /** @var string[] */
        private array $supportedJobNames,
    ) {
    }

    /**
     * @return string[]
     */
    public function getDefaultValues(): array
    {
        $defaultValues = $this->simpleProvider->getDefaultValues();
        $defaultValues['error_action'] = 'skip_product';
        $defaultValues['import_structure'] = [
            'columns' => [],
            'data_mappings' => [],
        ];
        $defaultValues['file_key'] = null;
        $defaultValues['file_structure'] = [
            'header_row' => 1,
            'first_column' => 0,
            'first_product_row' => 2,
            'unique_identifier_column' => 0,
            'sheet_name' => null,
        ];

        return $defaultValues;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
