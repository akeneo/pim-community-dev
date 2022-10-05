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

use Symfony\Component\Validator\Constraints\Collection;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Symfony\Component\Validator\Constraints;

class Evaluation implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface
{
    public const EVALUATED_SINCE_PARAMETER = 'evaluated_since';
    public const EVALUATED_SINCE_DATE_FORMAT = 'Y-m-d H:i:s';
    public const EVALUATED_SINCE_DEFAULT_TIME = '-1 DAY';

    public function getConstraintCollection(): Collection
    {
        $dateConstraint = new Constraints\DateTime();
        $dateConstraint->format = self::EVALUATED_SINCE_DATE_FORMAT;

        return new Collection(
            [
                'fields' => [
                    self::EVALUATED_SINCE_PARAMETER => $dateConstraint,
                ],
            ]
        );
    }

    public function getDefaultValues(): array
    {
        return [
            self::EVALUATED_SINCE_PARAMETER => (new \DateTime(self::EVALUATED_SINCE_DEFAULT_TIME))->format(self::EVALUATED_SINCE_DATE_FORMAT),
        ];
    }

    public function supports(JobInterface $job)
    {
        return $job->getName() === 'data_quality_insights_evaluations';
    }
}
