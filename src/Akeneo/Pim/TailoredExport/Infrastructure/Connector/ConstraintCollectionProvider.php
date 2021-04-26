<?php

declare(strict_types=1);

namespace Akeneo\Pim\TailoredExport\Infrastructure\Connector;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints\Collection;

class ConstraintCollectionProvider implements ConstraintCollectionProviderInterface
{
    protected ConstraintCollectionProviderInterface $simpleProvider;

    /** @var string[] */
    protected array $supportedJobNames;

    /**
     * @param string[] $supportedJobNames
     */
    public function __construct(
        ConstraintCollectionProviderInterface $simpleProvider,
        array $supportedJobNames
    ) {
        $this->simpleProvider = $simpleProvider;
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection(): Collection
    {
        return $this->simpleProvider->getConstraintCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
