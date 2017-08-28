<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamworkAssistant\Job\ProjectCalculation;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use PimEnterprise\Component\TeamworkAssistant\Validator\Constraints\ProjectIdentifier;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectCalculationJobParameters implements DefaultValuesProviderInterface, ConstraintCollectionProviderInterface
{
    /** @var string */
    protected $projectCalculationJobName;

    /**
     * @param $projectCalculationJobName
     */
    public function __construct($projectCalculationJobName)
    {
        $this->projectCalculationJobName = $projectCalculationJobName;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValues()
    {
        return ['notification_user' => null];
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection()
    {
        return new Collection([
            'fields' => [
                'project_code' => new ProjectIdentifier(),
                'notification_user' => new Type('string'),
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
