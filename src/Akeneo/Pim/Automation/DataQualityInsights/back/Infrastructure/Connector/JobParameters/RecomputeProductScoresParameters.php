<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Symfony\Component\Validator\Constraints;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RecomputeProductScoresParameters implements ConstraintCollectionProviderInterface, DefaultValuesProviderInterface
{
    public const LAST_PRODUCT_ID = 'lastProductId';

    public function getConstraintCollection(): Constraints\Collection
    {
        return new Constraints\Collection(
            [
                'fields' => [
                    self::LAST_PRODUCT_ID => new Constraints\Type('integer'),
                ],
            ]
        );
    }

    public function getDefaultValues(): array
    {
        return [
            self::LAST_PRODUCT_ID => 0,
        ];
    }

    public function supports(JobInterface $job): bool
    {
        return $job->getName() === 'data_quality_insights_recompute_products_scores';
    }
}
