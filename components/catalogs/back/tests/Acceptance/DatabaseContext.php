<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Acceptance;

use Behat\Behat\Context\Context;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use League\Flysystem\StorageAttributes;
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
        $this->resetCatalogMappingFilesystem();
        $catalog = $this->container->get('akeneo_integration_tests.catalogs');
        $configuration = $catalog->useMinimalCatalog();
        $fixturesLoader = $this->container->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load($configuration);
    }

    private function resetCatalogMappingFilesystem(): void
    {
        /** @var Filesystem $catalogMappingFilesystem */
        $catalogMappingFilesystem = $this->container->get('oneup_flysystem.catalogs_mapping_filesystem');

        $paths = $catalogMappingFilesystem->listContents('/')->filter(
            fn (StorageAttributes $attributes): bool => $attributes instanceof FileAttributes,
        )->map(
            fn (FileAttributes $attributes): string => $attributes->path(),
        );

        foreach ($paths as $path) {
            $catalogMappingFilesystem->delete($path);
        }
    }
}
