<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\FeatureFlag;

use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\InMemoryFeatureFlags;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

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
     * @BeforeScenario
     */
    public function enabledFeatureFlags(BeforeScenarioScope $scope)
    {
        $tags = [...$scope->getScenario()->getTags(), ...$scope->getFeature()->getTags()];
        $featureFlagTags = array_filter($tags, fn (string $tag) => preg_match('/-feature-enabled$/', $tag));
        $featureFlagsTagsWithoutSuffix = array_map(fn ($tag) => str_replace('-feature-enabled', '', $tag), $featureFlagTags);
        $featureFlags = array_map(fn ($tag) => str_replace('-', '_', $tag), $featureFlagsTagsWithoutSuffix);

        foreach ($featureFlags as $featureFlag) {
            $this->featureFlags->enable($featureFlag);
        }
    }
}
