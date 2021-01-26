<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Symfony\Component\Validator\Constraints;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
