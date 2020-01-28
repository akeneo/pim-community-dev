<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Symfony\Component\Validator\Constraints;

final class EvaluateProductsCriteriaParameters implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface
{
    public const PRODUCT_IDS = 'product_ids';

    public function getConstraintCollection(): Constraints\Collection
    {
        return new Constraints\Collection(
            [
                'fields' => [
                    self::PRODUCT_IDS => new Constraints\Type('array'),
                ],
            ]
        );
    }

    public function getDefaultValues(): array
    {
        return [
            self::PRODUCT_IDS => [],
        ];
    }

    public function supports(JobInterface $job)
    {
        return $job->getName() === 'data_quality_insights_evaluate_products_criteria';
    }
}
