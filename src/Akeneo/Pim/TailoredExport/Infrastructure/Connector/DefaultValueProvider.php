<?php

declare(strict_types=1);


namespace Akeneo\Pim\TailoredExport\Infrastructure\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;

class DefaultValueProvider implements DefaultValuesProviderInterface
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
        return $this->simpleProvider->getDefaultValues();
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
