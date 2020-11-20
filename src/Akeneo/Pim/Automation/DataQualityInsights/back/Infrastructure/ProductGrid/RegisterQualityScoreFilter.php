<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RegisterQualityScoreFilter
{
    private FeatureFlag $featureFlag;

    public function __construct(FeatureFlag $featureFlag)
    {
        $this->featureFlag = $featureFlag;
    }

    public function buildBefore(BuildBefore $event): void
    {
        $datagridConfiguration = $event->getConfig();

        if ('product-grid' !== $datagridConfiguration->getName()) {
            return;
        }

        if (!$this->featureFlag->isEnabled()) {
            return;
        }

        $filters = $datagridConfiguration->offsetGet(Configuration::FILTERS_KEY);
        $filters['columns']['data_quality_insights_score'] = $this->getEnrichmentFilter();

        $datagridConfiguration->offsetAddToArray(Configuration::FILTERS_KEY, $filters);
    }

    private function getEnrichmentFilter(): array
    {
        return [
            'type' => 'data_quality_insights_score',
            'ftype' => 'choice',
            'label' => 'akeneo_data_quality_insights.product_grid.filter_label.quality_score',
            'data_name' => 'data_quality_insights_score',
            'options' => [
                'field_options' => [
                    'multiple' => true,
                    'choices' => array_flip(Rank::LETTERS_MAPPING),
                ],
            ],
        ];
    }
}
