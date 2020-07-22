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

final class EvaluationsParameters implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface
{
    public const EVALUATE_FROM_FIELD = 'evaluate_from';
    public const EVALUATE_FROM_FORMAT = 'Y-m-d H:i:s';
    public const EVALUATE_FROM_DEFAULT_TIME = '-1 DAY';

    public function getConstraintCollection(): Constraints\Collection
    {
        $dateConstraint = new Constraints\DateTime();
        $dateConstraint->format = self::EVALUATE_FROM_FORMAT;

        return new Constraints\Collection(
            [
                'fields' => [
                    self::EVALUATE_FROM_FIELD => $dateConstraint,
                ],
            ]
        );
    }

    public function getDefaultValues(): array
    {
        return [
            self::EVALUATE_FROM_FIELD => (new \DateTime(self::EVALUATE_FROM_DEFAULT_TIME))->format(self::EVALUATE_FROM_FORMAT),
        ];
    }

    public function supports(JobInterface $job)
    {
        return $job->getName() === 'data_quality_insights_evaluations';
    }
}
