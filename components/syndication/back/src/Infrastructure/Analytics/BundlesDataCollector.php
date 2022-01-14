<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Syndication\Infrastructure\Analytics;

use Akeneo\Platform\Bundle\AnalyticsBundle\DataCollector\BundlesDataCollector as BaseBundlesDataCollector;

/**
 * The sole purpose of this class is to remove the syndication bundle from exposed bundles in EE
 */
class BundlesDataCollector extends BaseBundlesDataCollector
{
    /**
     * {@inheritdoc}
     *
     * Collect the list of registered bundles and exclude Syndication bundle from it
     */
    public function collect()
    {
        $bundles = $this->bundles;
        natsort($bundles);

        return ['registered_bundles' => array_filter(array_values($bundles), fn ($bundle) => 'Akeneo\\Platform\\Syndication\\Infrastructure\\Symfony\\AkeneoSyndicationBundle' !== $bundle)];
    }
}
