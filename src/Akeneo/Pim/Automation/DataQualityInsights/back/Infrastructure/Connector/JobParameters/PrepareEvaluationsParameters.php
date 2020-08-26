<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Symfony\Component\Validator\Constraints;

final class PrepareEvaluationsParameters implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface
{
    public const EXECUTED_FROM_FIELD = 'executed_from';
    public const EXECUTED_FROM_FORMAT = 'Y-m-d H:i:s';
    public const EXECUTED_FROM_DEFAULT_TIME = '-1 DAY';

    public const JOB_NAME = 'data_quality_insights_prepare_evaluations';

    public function getConstraintCollection(): Constraints\Collection
    {
        $dateConstraint = new Constraints\DateTime();
        $dateConstraint->format = self::EXECUTED_FROM_FORMAT;

        return new Constraints\Collection(
            [
                'fields' => [
                    self::EXECUTED_FROM_FIELD => $dateConstraint,
                ],
            ]
        );
    }

    public function getDefaultValues(): array
    {
        return [
            self::EXECUTED_FROM_FIELD => (new \DateTime(self::EXECUTED_FROM_DEFAULT_TIME))->format(self::EXECUTED_FROM_FORMAT),
        ];
    }

    public function supports(JobInterface $job)
    {
        return $job->getName() === self::JOB_NAME;
    }
}
