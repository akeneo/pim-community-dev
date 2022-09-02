<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Test\IntegrationTestsBundle\Loader;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Loader\FixturesLoaderInterface;

/**
 * Override of the CE fixtures loader to add permissions cleaning.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FixturesLoader implements FixturesLoaderInterface
{
    public function __construct(
        private FixturesLoaderInterface $baseFixturesLoader,
        private PermissionCleaner $permissionCleaner,
        private FeatureFlags $featureFlags
    )
    {
    }

    public function load(Configuration $configuration): void
    {
        $this->baseFixturesLoader->load($configuration);

        if ($this->featureFlags->isEnabled('permission')) {
            $this->permissionCleaner->cleanPermission();
        }
    }

    public function purge(): void
    {
        $this->baseFixturesLoader->purge();
    }
}
