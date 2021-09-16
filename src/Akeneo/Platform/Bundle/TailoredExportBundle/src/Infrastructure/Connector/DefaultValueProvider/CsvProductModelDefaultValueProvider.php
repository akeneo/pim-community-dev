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

class CsvProductModelDefaultValueProvider implements DefaultValuesProviderInterface
{
    private DefaultValuesProviderInterface $simpleProvider;
    /** @var string[] */
    private array $supportedJobNames;

    /**
     * @param string[] $supportedJobNames
     */
    public function __construct(
        DefaultValuesProviderInterface $simpleProvider,
        array $supportedJobNames
    ) {
        $this->simpleProvider = $simpleProvider;
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * @return array<string>
     */
    public function getDefaultValues(): array
    {
        $defaultValues = $this->simpleProvider->getDefaultValues();
        $defaultValues['with_media'] = true;
        $defaultValues['delimiter'] = ',';
        $defaultValues['enclosure'] = '"';
        $defaultValues['columns'] = [];
        $defaultValues['filters'] = [
            'data' => [
                [
                    'field'    => 'categories',
                    'operator' => Operators::NOT_IN_LIST,
                    'value'    => []
                ]
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
