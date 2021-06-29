<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
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

    private FeatureFlag $featureFlag;

    private string $filterName;

    private string $filterLabel;

    public function __construct(FeatureFlag $featureFlag, string $filterName, string $filterLabel)
    {
        $this->featureFlag = $featureFlag;
        $this->filterName = $filterName;
        $this->filterLabel = $filterLabel;
    }

    public function buildBefore(BuildBefore $event): void
    {
        $datagridConfiguration = $event->getConfig();

        if (!$this->isProductDatagrid($datagridConfiguration)) {
            return;
        }

        if (!$this->featureFlag->isEnabled()) {
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
