<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Acceptance;

use Behat\Behat\Context\Context;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatabaseContext implements Context
{
    private ContainerInterface $container;

    public function __construct(
        KernelInterface $kernel,
    ) {
        $this->container = $kernel->getContainer()->get('test.service_container');
    }

    /**
     * @BeforeScenario @database
     */
    public function loadFixtures(): void
    {
        $this->container->get('feature_flags')->enable('catalogs');
        $catalog = $this->container->get('akeneo_integration_tests.catalogs');
        $configuration = $catalog->useMinimalCatalog();
        $fixturesLoader = $this->container->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load($configuration);
    }
}
