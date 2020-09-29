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
    public const UPDATED_SINCE_PARAMETER = 'updated_since';
    public const UPDATED_SINCE_DATE_FORMAT = 'Y-m-d H:i:s';
    public const UPDATED_SINCE_DEFAULT_TIME = '-1 DAY';

    public function getConstraintCollection(): Constraints\Collection
    {
        $dateConstraint = new Constraints\DateTime();
        $dateConstraint->format = self::UPDATED_SINCE_DATE_FORMAT;

        return new Constraints\Collection(
            [
                'fields' => [
                    self::UPDATED_SINCE_PARAMETER => $dateConstraint,
                ],
            ]
        );
    }

    public function getDefaultValues(): array
    {
        return [
            self::UPDATED_SINCE_PARAMETER => (new \DateTime(self::UPDATED_SINCE_DEFAULT_TIME))->format(self::UPDATED_SINCE_DATE_FORMAT),
        ];
    }

    public function supports(JobInterface $job)
    {
        return $job->getName() === 'data_quality_insights_prepare_evaluations';
    }
}
