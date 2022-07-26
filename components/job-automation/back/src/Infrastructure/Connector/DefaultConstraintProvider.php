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

use Akeneo\Platform\JobAutomation\Infrastructure\Validation\Automation\Automation;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints\Collection;

final class DefaultConstraintProvider implements ConstraintCollectionProviderInterface
{
    public function __construct(private ConstraintCollectionProviderInterface $constraintProvider)
    {
    }

    public function getConstraintCollection(): Collection
    {
        $baseConstraint = $this->constraintProvider->getConstraintCollection();
        $constraintFields = $baseConstraint->fields;

        $constraintFields['automation'] = new Automation();

        return new Collection(['fields' => $constraintFields]);
    }

    public function supports(JobInterface $job)
    {
        return $this->constraintProvider->supports($job);
    }
}
