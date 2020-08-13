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

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration as FormatterConfiguration;

class RegisterQualityColumn
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

        $column = [
            'quality' => [
                'label' => 'akeneo_data_quality_insights.attribute_grid.quality_column_label',
                'data_name' => 'quality',
                'frontend_type' => 'quality-badge',
            ]
        ];

        $config->offsetAddToArrayByPath(sprintf('[%s]', FormatterConfiguration::COLUMNS_KEY), $column);
    }

    private function isApplicable(DatagridConfiguration $config): bool
    {
        return $this->featureFlag->isEnabled() && 'attribute-grid' === $config->getName();
    }
}
