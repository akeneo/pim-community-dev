<?php

declare(strict_types=1);

namespace Akeneo\Test\Integration;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Test\IntegrationTestsBundle\Configuration\CatalogInterface;
use Akeneo\Test\IntegrationTestsBundle\Security\SystemUserAuthenticator;
use Akeneo\Tool\Component\FileStorage\FileInfoFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class TestCase extends KernelTestCase
{
    /** @var KernelInterface */
    protected $testKernel;

    /** @var CatalogInterface */
    protected $catalog;

    /**
     * @return Configuration
     */
    abstract protected function getConfiguration();

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        static::bootKernel(['debug' => false]);
        $this->testKernel = new \AppKernelTest('test', false);
        $this->testKernel->boot();

        $this->catalog = $this->testKernel->getContainer()->get('akeneo_integration_tests.configuration.catalog');
        if (null !== $this->getConfiguration()) {
            $this->testKernel->getContainer()->set('akeneo_integration_tests.catalog.configuration', $this->getConfiguration());
            $fixturesLoader = $this->testKernel->getContainer()->get('akeneo_integration_tests.loader.fixtures_loader');
            $fixturesLoader->load();
        }

        // authentication should be done after loading the database as the user is created with first activated locale as default locale
        $authenticator = new SystemUserAuthenticator(static::$kernel->getContainer());
        $authenticator->createSystemUser();
        $this->get('doctrine.orm.default_entity_manager')->clear();

    }

    /**
     * @param string $service
     *
     * @return mixed
     */
    protected function get(string $service)
    {
        return static::$kernel->getContainer()->get($service);
    }

    /**
     * @param string $service
     *
     * @return mixed
     */
    protected function getFromTestContainer(string $service)
    {
        return $this->testKernel->getContainer()->get($service);
    }

    /**
     * @param string $parameter
     *
     * @return mixed
     */
    protected function getParameter(string $parameter)
    {
        return static::$kernel->getContainer()->getParameter($parameter);
    }

    /**
     * @param string $parameter
     *
     * @return bool
     */
    protected function hasParameter(string $parameter)
    {
        return static::$kernel->getContainer()->hasParameter($parameter);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $connectionCloser = $this->testKernel->getContainer()->get('akeneo_integration_tests.doctrine.connection.connection_closer');
        $connectionCloser->closeConnections();

        $this->esClient = null;
        $this->esConfigurationLoader = null;

        parent::tearDown();
    }

    /**
     * Look in every fixture directory if a fixture $name exists.
     * And return the pathname of the fixture if it exists.
     *
     * @param string $name
     *
     * @throws \Exception if no fixture $name has been found
     *
     * @return string
     */
    protected function getFixturePath(string $name): string
    {
        $configuration = $this->getConfiguration();
        foreach ($configuration->getFixtureDirectories() as $fixtureDirectory) {
            $path = $fixtureDirectory . DIRECTORY_SEPARATOR . $name;
            if (is_file($path) && false !== realpath($path)) {
                return realpath($path);
            }
        }

        throw new \Exception(sprintf('The fixture "%s" does not exist.', $name));
    }

    protected function getFileInfoKey(string $path): string
    {
        if (!is_file($path)) {
            throw new \Exception(sprintf('The path "%s" does not exist.', $path));
        }

        $fileStorer = $this->get('akeneo_file_storage.file_storage.file.file_storer');
        $fileInfo = $fileStorer->store(new \SplFileInfo($path), FileStorage::CATALOG_STORAGE_ALIAS);

        return $fileInfo->getKey();
    }

    /**
     * @param array $data
     *
     * @return CategoryInterface
     */
    protected function createCategory(array $data = []): CategoryInterface
    {
        $category = $this->get('pim_catalog.factory.category')->create();
        $this->get('pim_catalog.updater.category')->update($category, $data);
        $this->get('validator')->validate($category);
        $this->get('pim_catalog.saver.category')->save($category);

        return $category;
    }
}
