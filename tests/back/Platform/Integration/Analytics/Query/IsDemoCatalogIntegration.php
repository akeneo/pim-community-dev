<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\Analytics\Query;

use Akeneo\Platform\Bundle\InstallerBundle\FixtureLoader\FixtureJobLoader;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Component\Analytics\IsDemoCatalogQuery;
use PHPUnit\Framework\Assert;

final class IsDemoCatalogIntegration extends TestCase
{
    private IsDemoCatalogQuery $isDemoCatalogQuery;
    private JobLauncher $jobLauncher;
    private FixtureJobLoader $fixtureJobLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->isDemoCatalogQuery = $this->get('pim_analytics.query.is_demo_catalog');
        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->fixtureJobLoader = $this->get('pim_installer.fixture_loader.job_loader');
    }

    /**
     *
     * Testing this query by installing an Icecat catalog (current demo catalog) would take too much time.
     * Instead, the strategy is to only install the user fixtures from the Icecat catalog.
     *
     * If the email of the users changed in the Icecat fixtures, this test would probably be red.
     * In that case, please modify the query to guess if it's a demo catalog.
     */

    public function test_the_query_return_true_when_it_is_has_users_from_demo_catalog()
    {
        Assert::assertFalse($this->isDemoCatalogQuery->fetch());

        // test both CE and EE fixtures as the path is the same
        $this->fixtureJobLoader->loadJobInstances($this->getParameter('kernel.project_dir') . '/src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/minimal');
        $fixturePath = $this->getParameter('kernel.project_dir') . '/src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/icecat_demo_dev/';

        $this->jobLauncher->launchImport('fixtures_channel_csv', file_get_contents($fixturePath . 'channels.csv'));
        $this->jobLauncher->launchImport('fixtures_user_csv', file_get_contents($fixturePath . 'users.csv'));

        Assert::assertTrue($this->isDemoCatalogQuery->fetch());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
