<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\PimVersion;

use Akeneo\Platform\Bundle\PimVersionBundle\Version\PimVersion;
use Akeneo\Platform\Bundle\PimVersionBundle\VersionProvider;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class VersionProviderIntegration extends KernelTestCase
{
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
            [new TestCommunityVersion()],
            'community_edition_instance',
            __DIR__
        );

        Assert::assertSame('CE', $versionProvider->getEdition());
    }

    public function test_it_provides_ce_version()
    {
        $versionProvider = new VersionProvider(
            [new TestCommunityVersion()],
            'community_edition_instance',
            __DIR__
        );

        Assert::assertSame('12.42.20-BETA2', $versionProvider->getVersion());
    }

    public function test_it_provides_full_ce_version()
    {
        $versionProvider = new VersionProvider(
            [new TestCommunityVersion()],
            'community_edition_instance',
            __DIR__
        );
        Assert::assertSame('CE 12.42.20-BETA2 STATIC TEST VERSION', $versionProvider->getFullVersion());
    }

    public function test_that_it_provides_the_community_edition_version()
    {
        $_ENV['PIM_EDITION'] = 'community_edition_instance';

        $versionProvider = static::$kernel->getContainer()->get('pim_catalog.version_provider');

        Assert::assertSame('CE', $versionProvider->getEdition());
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

    public function isEditionCode(string $editionCode): bool
    {
        return $editionCode === 'community_edition_instance';
    }
}
