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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector\DefaultValueProvider;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;

class XlsxProductDefaultValueProvider implements DefaultValuesProviderInterface
{
    /**
     * @param string[] $supportedJobNames
     */
    public function __construct(
        private DefaultValuesProviderInterface $simpleProvider,
        private array $supportedJobNames,
    ) {
    }

    /**
     * @return array<string>
     */
    public function getDefaultValues(): array
    {
        $defaultValues = $this->simpleProvider->getDefaultValues();
        $defaultValues['with_media'] = true;
        $defaultValues['columns'] = [];
        $defaultValues['filters'] = [
            'data' => [
                [
                    'field' => 'enabled',
                    'operator' => Operators::EQUALS,
                    'value' => true,
                ],
                [
                    'field' => 'categories',
                    'operator' => Operators::NOT_IN_LIST,
                    'value' => [],
                ],
                [
                    'field' => 'completeness',
                    'operator' => 'ALL',
                    'value' => 100,
                ],
            ],
        ];

        return $defaultValues;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
