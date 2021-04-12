<?php

declare(strict_types=1);

namespace Akeneo\Pim\TailoredExport\Connector;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConstraintCollectionProvider implements ConstraintCollectionProviderInterface
{
    protected ConstraintCollectionProviderInterface $simpleProvider;
    protected array $supportedJobNames;

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
        $baseConstraint = $this->simpleProvider->getConstraintCollection();
        $constraintFields = $baseConstraint->fields;
        $constraintFields['filters'] = [
            new Collection([
                'fields' => ['data' => new NotBlank()],
                'allowExtraFields' => true
            ])
        ];

        return new Collection(['fields' => $constraintFields]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
