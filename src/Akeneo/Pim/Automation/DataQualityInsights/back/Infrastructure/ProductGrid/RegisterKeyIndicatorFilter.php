<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterKeyIndicatorFilter
{
    public const PRODUCT_DATAGRID_NAME = 'product-grid';

    public function __construct(
        private FeatureFlag $dqiFeature,
        private FeatureFlags $featureFlags,
        private string $filterName,
        private string $filterLabel,
        private ?string $featureName,
    ) {
    }

    // TIP-1555: to remove later on with AddDraftStatusFilterToProductGridListener also
    public function buildBefore(BuildBefore $event): void
    {
        $datagridConfiguration = $event->getConfig();

        if (!$this->isProductDatagrid($datagridConfiguration)) {
            return;
        }

        if (!$this->dqiFeature->isEnabled()) {
            return;
        }

        if (null !== $this->featureName && !$this->featureFlags->isEnabled($this->featureName)) {
            return;
        }

        $filters = $datagridConfiguration->offsetGet(Configuration::FILTERS_KEY);
        $filters['columns'][$this->filterName] = $this->buildFilter();

        $datagridConfiguration->offsetAddToArray(Configuration::FILTERS_KEY, $filters);
    }

    private function buildFilter(): array
    {
        return [
            'type' => $this->filterName,
            'ftype' => 'choice',
            'label' => $this->filterLabel,
            'data_name' => $this->filterName,
            'options' => [
                'field_options' => [
                    'choices' => $this->getFilterChoices(),
                ],
            ],
        ];
    }

    private function getFilterChoices(): array
    {
        $defaultChoices = [
            'akeneo_data_quality_insights.product_grid.filter_value.good' => true,
            'akeneo_data_quality_insights.product_grid.filter_value.to_improve' => false,
        ];

        $choices = $defaultChoices;

        if ('data_quality_insights_images_quality' === $this->filterName) {
            $choices = [
                'akeneo_data_quality_insights.product_grid.filter_value.yes' => true,
                'akeneo_data_quality_insights.product_grid.filter_value.no' => false,
            ];
        }

        return $choices;
    }

    private function isProductDatagrid(DatagridConfiguration $datagridConfiguration): bool
    {
        return self::PRODUCT_DATAGRID_NAME === $datagridConfiguration->getName();
    }
}
