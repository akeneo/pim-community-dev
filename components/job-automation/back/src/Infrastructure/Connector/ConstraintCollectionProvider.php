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

namespace Akeneo\Platform\JobAutomation\Infrastructure\Connector;

use Akeneo\Platform\JobAutomation\Infrastructure\Validation\Storage;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints\Collection;

class ConstraintCollectionProvider implements ConstraintCollectionProviderInterface
{
    public function __construct(
        private ConstraintCollectionProviderInterface $overriddenProvider,
        /** @var string[] */
        private array $supportedJobNames,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection(): Collection
    {
        $baseConstraint = $this->overriddenProvider->getConstraintCollection();
        $constraintFields = $baseConstraint->fields;

        $constraintFields['storage'] = new Storage(['xlsx', 'xls']);

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
