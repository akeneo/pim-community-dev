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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\ProductGrid;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rank;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;

class RegisterConsistencyFilter
{
    public const PRODUCT_DATAGRID_NAME = 'product-grid';

    /** @var FeatureFlag */
    private $featureFlag;

    public function __construct(FeatureFlag $featureFlag)
    {
        $this->featureFlag = $featureFlag;
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
        $filters['columns']['data_quality_insights_consistency'] = $this->getConsistencyFilter();

        $datagridConfiguration->offsetAddToArray(Configuration::FILTERS_KEY, $filters);
    }

    private function getConsistencyFilter(): array
    {
        return [
            'type' => 'data_quality_insights_consistency',
            'ftype' => 'choice',
            'label' => 'akeneo_data_quality_insights.axis.consistency',
            'data_name' => 'data_quality_insights_consistency',
            'options' => [
                'field_options' => [
                    'multiple' => true,
                    'choices' => array_flip(Rank::LETTERS_MAPPING),
                ],
            ],
        ];
    }

    private function isProductDatagrid(DatagridConfiguration $datagridConfiguration): bool
    {
        return self::PRODUCT_DATAGRID_NAME === $datagridConfiguration->getName();
    }
}
