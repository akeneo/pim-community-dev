<?php

namespace AkeneoTest\Platform\Integration\FeatureFlag;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Registry;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FilePersistedFeatureFlagsIntegration extends KernelTestCase
{
    function test_it_is_disabled_by_default_when_asking_for_an_existing_feature_flag()
    {
        $featureFlags = $this->initializeFeatureFlag();
        Assert::assertFalse($featureFlags->isEnabled('foo'));
        Assert::assertFalse($featureFlags->isEnabled('bar'));
    }

    function test_it_is_enabled_only_if_explicitly_enable()
    {
        $featureFlags = $this->initializeFeatureFlag();
        $featureFlags->enable('bar');
        Assert::assertFalse($featureFlags->isEnabled('foo'));
        Assert::assertTrue($featureFlags->isEnabled('bar'));
    }

    function test_it_throws_an_exception_if_the_feature_does_not_exist()
    {
        $featureFlags = $this->initializeFeatureFlag();
        $this->expectException(\InvalidArgumentException::class);
        $featureFlags->isEnabled('baz');
    }

    private function initializeFeatureFlag(): FilePersistedFeatureFlags
    {
        $registry = new Registry();
        $registry->add('foo', new Enabled());
        $registry->add('bar', new Disabled());

        $featureFlags = new FilePersistedFeatureFlags($registry, sys_get_temp_dir() . '/');
        $featureFlags->deleteFile();

        return $featureFlags;
    }
}


class Enabled implements FeatureFlag
{
    public function isEnabled(?string $feature = null): bool
    {
        return true;
    }
}

class Disabled implements FeatureFlag
{
    public function isEnabled(?string $feature = null): bool
    {
        return false;
    }
}
