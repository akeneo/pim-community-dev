<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\Version;

use Akeneo\Platform\EnterpriseVersion;
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

    public function test_that_it_provides_the_enterprise_version()
    {
        $versionProvider = static::$kernel->getContainer()->get('pim_catalog.version_provider');

        $expected = sprintf(
            '%s %s %s',
            EnterpriseVersion::EDITION,
            EnterpriseVersion::VERSION,
            EnterpriseVersion::VERSION_CODENAME
        );
        Assert::assertSame($expected, $versionProvider->getFullVersion());
    }
}
