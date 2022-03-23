<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use Akeneo\Pim\Structure\Component\Factory\FamilyFactory;
use Akeneo\Pim\Structure\Component\Updater\FamilyUpdater;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\InMemoryFeatureFlags;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FeatureFlagContext implements Context
{
    public function __construct(
        private InMemoryFeatureFlags $featureFlags
    ) {
    }

    /**
     * @BeforeScenario asset-manager-feature-enabled
     */
    public function enabledAssetManagerFeatureFlag()
    {
        $this->featureFlags->enable('asset_manager');
    }
}
