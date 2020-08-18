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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\AttributeGrid;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\Quality;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration as FilterConfiguration;

class RegisterQualityFilter
{
    /** @var FeatureFlag */
    private $featureFlag;

    public function __construct(FeatureFlag $featureFlag)
    {
        $this->featureFlag = $featureFlag;
    }

    public function buildBefore(BuildBefore $event): void
    {
        $config = $event->getConfig();

        if (!$this->isApplicable($config)) {
            return;
        }

        $filter = [
            'quality' => [
                'type' => 'data_quality_insights_attribute_quality_filter',
                'ftype' => 'choice',
                'label' => 'Quality',
                'data_name' => 'quality',
                'options' => [
                    'field_options' => [
                        'multiple' => false,
                        'choices' => [
                            'akeneo_data_quality_insights.attribute_grid.quality.good' => Quality::GOOD,
                            'akeneo_data_quality_insights.attribute_grid.quality.to_improve' => Quality::TO_IMPROVE,
                        ],
                    ],
                ],
            ]
        ];

        $config->offsetAddToArrayByPath(FilterConfiguration::COLUMNS_PATH, $filter);
    }

    private function isApplicable(DatagridConfiguration $config): bool
    {
        return $this->featureFlag->isEnabled() && 'attribute-grid' === $config->getName();
    }
}
