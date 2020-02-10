<?php

namespace Context;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Loader\FixturesLoader;
use Akeneo\Test\IntegrationTestsBundle\Loader\FixturesLoaderInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Pim\Behat\Context\PimContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A context for initializing catalog configuration
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CatalogConfigurationContext extends PimContext
{
    /** @var string Catalog configuration path */
    private $catalogPath = 'catalog';

    /** @var string[] Additional catalog configuration directories */
    private $extraDirectories = [];

    /** @var FixturesLoaderInterface */
    private $fixturesLoader;

    /** @var EntityManagerClearerInterface */
    private $entityManagerClearer;

    public function __construct(
        string $mainContextClass,
        FixturesLoaderInterface $fixturesLoader,
        EntityManagerClearerInterface $entityManagerClearer,
        array $extraDirectories = []
    ) {
        parent::__construct($mainContextClass);

        $this->fixturesLoader = $fixturesLoader;
        $this->entityManagerClearer = $entityManagerClearer;
        $this->extraDirectories = $extraDirectories;
    }

    /**
     * @param string $catalog
     *
     * @Given /^(?:a|an|the) "([^"]*)" catalog configuration$/
     */
    public function aCatalogConfiguration(string $catalog)
    {
        $extraDirectoriesSuffixedWithCatalog = array_map(function (string $directory) use ($catalog) {
            return $directory . '/' . $catalog;
        }, $this->extraDirectories);

        $catalogDirectories = array_merge([__DIR__.'/'.$this->catalogPath . '/' . $catalog], $extraDirectoriesSuffixedWithCatalog);
        $existingCatalogDirectories = array_filter($catalogDirectories, function (string $directory) {
            return is_dir($directory);
        });

        if (empty($existingCatalogDirectories)) {
            throw new \LogicException('There is not any fixture directory configured to load the catalog.');
        }

        $this->fixturesLoader->load(new Configuration($existingCatalogDirectories));
        $this->entityManagerClearer->clear();
    }
}
