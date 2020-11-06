<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Validator\Constraints\ProjectIdentifier;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectCalculationJobParameters implements DefaultValuesProviderInterface, ConstraintCollectionProviderInterface
{
    protected string $projectCalculationJobName;

    public function __construct(string $projectCalculationJobName)
    {
        $this->projectCalculationJobName = $projectCalculationJobName;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValues()
    {
        return [
            'user_to_notify' => null,
            'is_user_authenticated' => false
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection()
    {
        return new Collection([
            'fields' => [
                'project_code' => new ProjectIdentifier(),
                'user_to_notify' => new Type('string'),
                'is_user_authenticated' => new Type('bool'),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return $this->projectCalculationJobName === $job->getName();
    }
}
