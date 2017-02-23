<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamWorkAssistant\Job\ProjectCalculation;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use PimEnterprise\Component\TeamWorkAssistant\Validator\ProjectIdentifier;
use Symfony\Component\Validator\Constraints\Collection;

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
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection()
    {
        return new Collection([
            'fields' => [
                'project_code' => new ProjectIdentifier()
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
