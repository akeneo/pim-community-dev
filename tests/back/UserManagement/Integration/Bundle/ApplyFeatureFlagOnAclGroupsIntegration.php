<?php

declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle;

use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeFeatureFlag;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\ImmutableFeatureFlags;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Registry;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Bundle\Twig\AclGroupsExtension;
use AkeneoTest\UserManagement\Integration\Fixtures\FeatureFlagOnAclGroupsTestBundle\FeatureFlagOnAclGroupsTestBundle;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ApplyFeatureFlagOnAclGroupsIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getConfiguration(): ?Configuration
    {
        return null;
    }

    public function testTheTwigExtensionHidesAclGroupsWithDisabledFeatureFlag()
    {
        $extension = $this->createAclGroupsExtension([
            'foo' => false,
        ]);

        $this->assertEquals([
            'action' => [
                [
                    'name' => 'test.acl_group.no_feature_flag',
                    'order' => 10,
                ],
            ],
        ], $extension->getAclGroups());
    }

    public function testTheTwigExtensionShowsAclGroupsWithEnabledFeatureFlag()
    {
        $extension = $this->createAclGroupsExtension([
            'foo' => true,
        ]);

        $this->assertEquals([
            'action' => [
                [
                    'name' => 'test.acl_group.no_feature_flag',
                    'order' => 10,
                ],
                [
                    'name' => 'test.acl_group.with_feature_flag',
                    'order' => 20,
                ],
            ],
        ], $extension->getAclGroups());
    }

    /**
     * @param array<string, bool> $flags
     */
    private function createAclGroupsExtension(array $flags): AclGroupsExtension
    {
        $registry = new Registry();
        foreach ($flags as $flag => $enabled) {
            $registry->add($flag, new FakeFeatureFlag($enabled));
        }
        $features = new ImmutableFeatureFlags($registry);
        return new AclGroupsExtension(
            [
                'FeatureFlagOnAclGroupsTestBundle' => FeatureFlagOnAclGroupsTestBundle::class,
            ],
            $features,
        );
    }
}
