<?php

declare(strict_types=1);


namespace Akeneo\Pim\TailoredExport\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;

class DefaultValueProvider implements DefaultValuesProviderInterface
{
    private DefaultValuesProviderInterface $simpleProvider;
    private array $supportedJobNames;

    public function __construct(
        DefaultValuesProviderInterface $simpleProvider,
        array $supportedJobNames
    ) {
        $this->simpleProvider = $simpleProvider;
        $this->supportedJobNames = $supportedJobNames;
    }

    public function getDefaultValues()
    {
        $parameters = $this->simpleProvider->getDefaultValues();
        $parameters['filters'] = [
            'data' => [
                [
                    'field' => 'enabled',
                    'operator' => Operators::EQUALS,
                    'value' => true,
                ],
                //                [
                //                    'field' => 'completeness',
                //                    'operator' => Operators::GREATER_OR_EQUAL_THAN,
                //                    'value' => 100,
                //                ],
                [
                    'field' => 'categories',
                    'operator' => Operators::IN_CHILDREN_LIST,
                    'value' => ['master']
                ]
            ],
        ];

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
