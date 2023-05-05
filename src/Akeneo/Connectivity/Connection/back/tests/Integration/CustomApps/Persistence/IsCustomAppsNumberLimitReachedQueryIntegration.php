<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\CustomApps\Persistence;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\IsCustomAppsNumberLimitReachedQuery;
use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Service\GetCustomAppsNumberLimit;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\CustomAppLoader;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsCustomAppsNumberLimitReachedQueryIntegration extends WebTestCase
{
    private IsCustomAppsNumberLimitReachedQuery $customAppsNumberLimitReachedQuery;
    private GetCustomAppsNumberLimit $getCustomAppsNumberLimit;
    private ?FilePersistedFeatureFlags $featureFlags;
    private ?CustomAppLoader $customAppLoader;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->customAppsNumberLimitReachedQuery = $this->get(IsCustomAppsNumberLimitReachedQuery::class);
        $this->featureFlags = $this->get('feature_flags');
        $this->customAppLoader = $this->get(CustomAppLoader::class);

        $this->getCustomAppsNumberLimit = $this->get(GetCustomAppsNumberLimit::class);
        $this->getCustomAppsNumberLimit->setLimit(20);
    }

    public function test_it_returns_false_when_custom_apps_count_is_below_the_limit(): void
    {
        $result = $this->customAppsNumberLimitReachedQuery->execute();

        Assert::assertFalse($result);

        $this->featureFlags->enable('app_developer_mode');
        $user = $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_test_apps');
        $this->customAppLoader->create('111eedac-ff5c-497b-899d-e2d64b6c59f9', $user->getId());
        $this->customAppLoader->create('222eedac-ff5c-497b-899d-e2d64b6c59f9', $user->getId());
        $this->customAppLoader->create('333eedac-ff5c-497b-899d-e2d64b6c59f9', $user->getId());

        $result = $this->customAppsNumberLimitReachedQuery->execute();

        Assert::assertFalse($result);
    }

    public function test_it_returns_false_when_connection_count_is_above_the_limit(): void
    {
        $this->getCustomAppsNumberLimit->setLimit(3);

        $result = $this->customAppsNumberLimitReachedQuery->execute();

        Assert::assertFalse($result);

        $this->featureFlags->enable('app_developer_mode');
        $user = $this->authenticateAsAdmin();
        $this->addAclToRole('ROLE_ADMINISTRATOR', 'akeneo_connectivity_connection_manage_test_apps');
        $this->customAppLoader->create('111eedac-ff5c-497b-899d-e2d64b6c59f9', $user->getId());
        $this->customAppLoader->create('222eedac-ff5c-497b-899d-e2d64b6c59f9', $user->getId());
        $this->customAppLoader->create('333eedac-ff5c-497b-899d-e2d64b6c59f9', $user->getId());

        $result = $this->customAppsNumberLimitReachedQuery->execute();

        Assert::assertTrue($result);
    }
}
