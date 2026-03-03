<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\PimVersion;

use Akeneo\Platform\Bundle\PimVersionBundle\Version\PimVersion;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProvider;
use Akeneo\Platform\SerenityVersion;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class VersionProviderIntegration extends KernelTestCase
{
    private string $pimEdition;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        static::bootKernel(['debug' => false]);
    }

    public function test_it_provides_ce_edition()
    {
        $versionProvider = new VersionProvider(
            [new TestCommunityVersion(), new TestSerenityVersion()],
            'community_edition_instance',
            __DIR__
        );

        Assert::assertSame('CE', $versionProvider->getEdition());
    }

    public function test_it_provides_ce_version()
    {
        $versionProvider = new VersionProvider(
            [new TestCommunityVersion(), new TestSerenityVersion()],
            'community_edition_instance',
            __DIR__
        );

        Assert::assertSame('12.42.20-BETA2', $versionProvider->getVersion());
    }

    public function test_it_provides_ce_patch()
    {
        $versionProvider = new VersionProvider(
            [new TestCommunityVersion(), new TestSerenityVersion()],
            'community_edition_instance',
            __DIR__
        );
        Assert::assertSame('12.42.20', $versionProvider->getPatch());
    }

    public function test_it_provides_ce_minor_version()
    {
        $versionProvider = new VersionProvider(
            [new TestCommunityVersion(), new TestSerenityVersion()],
            'community_edition_instance',
            __DIR__
        );
        Assert::assertSame('12.42', $versionProvider->getMinorVersion());
    }

    public function test_it_provides_full_ce_version()
    {
        $versionProvider = new VersionProvider(
            [new TestCommunityVersion(), new TestSerenityVersion()],
            'community_edition_instance',
            __DIR__
        );
        Assert::assertSame('CE 12.42.20-BETA2 STATIC TEST VERSION', $versionProvider->getFullVersion());
    }

    public function test_it_tells_if_its_not_a_saas_version()
    {
        $versionProvider = new VersionProvider(
            [new TestCommunityVersion(), new TestSerenityVersion()],
            'community_edition_instance',
            __DIR__
        );
        Assert::assertFalse($versionProvider->isSaaSVersion());
    }

    public function test_it_provides_saas_edition()
    {
        $versionProvider = new VersionProvider(
            [new TestCommunityVersion(), new TestSerenityVersion()],
            'serenity_instance',
            __DIR__
        );
        Assert::assertSame('Serenity', $versionProvider->getEdition());
    }

    public function test_it_provides_saas_patch()
    {
        $versionProvider = new VersionProvider(
            [new TestCommunityVersion(), new TestSerenityVersion()],
            'serenity_instance',
            __DIR__
        );
        Assert::assertSame('12.42.20-BETA2', $versionProvider->getPatch());
    }

    public function test_it_provides_saas_minor_version()
    {
        $versionProvider = new VersionProvider(
            [new TestCommunityVersion(), new TestSerenityVersion()],
            'serenity_instance',
            __DIR__
        );
        Assert::assertSame('12.42.20-BETA2', $versionProvider->getMinorVersion());
    }

    public function test_it_provides_full_saas_version()
    {
        $versionProvider = new VersionProvider(
            [new TestCommunityVersion(), new TestSerenityVersion()],
            'serenity_instance',
            __DIR__
        );
        Assert::assertSame('Serenity 12.42.20-BETA2 STATIC TEST VERSION', $versionProvider->getFullVersion());
    }

    public function test_it_tells_if_its_a_saas_version()
    {
        $versionProvider = new VersionProvider(
            [new TestCommunityVersion(), new TestSerenityVersion()],
            'serenity_instance',
            __DIR__
        );
        Assert::assertTrue($versionProvider->isSaaSVersion());
    }


    public function test_that_it_provides_the_serenity_version()
    {
        $_ENV['PIM_EDITION'] = 'serenity_instance';

        $versionProvider = static::$kernel->getContainer()->get('pim_catalog.version_provider');

        Assert::assertSame('Serenity', $versionProvider->getEdition());
    }

    public function test_that_it_provides_the_growth_edition_version()
    {
        $_ENV['PIM_EDITION'] = 'growth_edition_instance';
        $versionProvider = static::$kernel->getContainer()->get('pim_catalog.version_provider');

        Assert::assertSame('Growth Edition', $versionProvider->getEdition());
    }

    /**
     * Do not change it during a pull up.
     * It is useful to hardcode it as master, as it allows to follow who installed CE master thanks to the PIM tracker analytics.
     *
     * Test to remove when tagging a major version.
     */
    public function test_that_it_provides_the_community_version()
    {
        $_ENV['PIM_EDITION'] = 'community_edition_instance';

        $versionProvider = static::$kernel->getContainer()->get('pim_catalog.version_provider');

        Assert::assertSame('CE', $versionProvider->getEdition());
        Assert::assertSame('master', $versionProvider->getVersion());
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($_ENV['PIM_EDITION']);
    }
}

class TestCommunityVersion implements PimVersion
{
    /** @staticvar string */
    const VERSION = '12.42.20-BETA2';

    /** @staticvar string */
    const VERSION_CODENAME = 'STATIC TEST VERSION';

    /** @staticvar string */
    const EDITION_NAME = 'CE';

    public function version(): string
    {
        return self::VERSION;
    }

    public function versionCodename(): string
    {
        return self::VERSION_CODENAME;
    }

    public function editionName(): string
    {
        return self::EDITION_NAME;
    }

    public function isSaas(): bool
    {
        return false;
    }

    public function isEditionCode(string $editionCode): bool
    {
        return $editionCode === 'community_edition_instance';
    }
}

class TestSerenityVersion implements PimVersion
{
    /** @staticvar string */
    const VERSION = '20200130151605';

    /** @staticvar string */
    const VERSION_CODENAME = 'STATIC TEST VERSION';

    /** @staticvar string */
    const EDITION_NAME = 'Serenity';

    public function version(): string
    {
        return self::VERSION;
    }

    public function versionCodename(): string
    {
        return self::VERSION_CODENAME;
    }

    public function editionName(): string
    {
        return self::EDITION_NAME;
    }

    public function isSaas(): bool
    {
        return true;
    }

    public function isEditionCode(string $editionCode): bool
    {
        return $editionCode === 'serenity_instance';
    }
}
