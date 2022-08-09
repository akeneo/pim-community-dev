<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Test\IntegrationTestsBundle\Configuration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Configuration\CatalogInterface;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Catalog implements CatalogInterface
{
    /** @var CatalogInterface */
    private $communityCatalog;

    /**
     * @param CatalogInterface $communityCatalog
     */
    public function __construct(CatalogInterface $communityCatalog)
    {
        $this->communityCatalog = $communityCatalog;
    }

    /**
     * {@inheritdoc}
     */
    public function useTechnicalSqlCatalog(): Configuration
    {
        $communityConfig = $this->communityCatalog->useTechnicalSqlCatalog();
        $catalogDirectories = [realpath(
            $this->getRootDirectory() .
            'tests' .
            DIRECTORY_SEPARATOR .
            'back' .
            DIRECTORY_SEPARATOR .
            'Integration' .
            DIRECTORY_SEPARATOR .
            'catalog' .
            DIRECTORY_SEPARATOR .
            'technical_sql'
        )];

        return new Configuration(
                array_merge($communityConfig->getCatalogDirectories(), $catalogDirectories),
                $communityConfig->getFixtureDirectories()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function useTechnicalCatalog(array $featureFlags = []): Configuration
    {
        $communityConfig = $this->communityCatalog->useTechnicalCatalog();
        $catalogDirectories = [realpath(
            $this->getRootDirectory() .
            'tests' .
            DIRECTORY_SEPARATOR .
            'back' .
            DIRECTORY_SEPARATOR .
            'Integration' .
            DIRECTORY_SEPARATOR .
            'catalog' .
            DIRECTORY_SEPARATOR .
            'technical'
        )];

        return new Configuration(
            array_merge($communityConfig->getCatalogDirectories(), $catalogDirectories),
            $communityConfig->getFixtureDirectories(),
            $featureFlags
        );
    }

    /**
     * {@inheritdoc}
     */
    public function useMinimalCatalog(array $featureFlags = []): Configuration
    {
        $communityConfig = $this->communityCatalog->useMinimalCatalog();
        $catalogDirectories = [realpath(
            $this->getRootDirectory() .
            'src' .
            DIRECTORY_SEPARATOR .
            'Akeneo' .
            DIRECTORY_SEPARATOR .
            'Platform' .
            DIRECTORY_SEPARATOR .
            'Bundle' .
            DIRECTORY_SEPARATOR .
            'InstallerBundle' .
            DIRECTORY_SEPARATOR .
            'Resources' .
            DIRECTORY_SEPARATOR .
            'fixtures' .
            DIRECTORY_SEPARATOR .
            'minimal'
        )];

        return new Configuration(
            $catalogDirectories,
            $communityConfig->getFixtureDirectories(),
            $featureFlags
        );

    }

    /**
     * {@inheritdoc}
     */
    public function useFunctionalCatalog(string $catalog, array $featureFlags = []): Configuration
    {
        $communityConfig = $this->communityCatalog->useFunctionalCatalog($catalog);
        $catalogDirectories = [realpath(
            $this->getRootDirectory() .
            DIRECTORY_SEPARATOR .
            'tests'.
            DIRECTORY_SEPARATOR .
            'legacy'.
            DIRECTORY_SEPARATOR .
            'features'.
            DIRECTORY_SEPARATOR .
            'Context' .
            DIRECTORY_SEPARATOR .
            'catalog'.
            DIRECTORY_SEPARATOR .
            $catalog
        )];

        $fixtureDirectories = [realpath(
            $this->getRootDirectory() .
            DIRECTORY_SEPARATOR .
            'tests'.
            DIRECTORY_SEPARATOR .
            'legacy'.
            DIRECTORY_SEPARATOR .
            'features'.
            DIRECTORY_SEPARATOR .
            'Context' .
            DIRECTORY_SEPARATOR .
            'fixtures'
        )];


        return new Configuration(
            array_merge($communityConfig->getCatalogDirectories(), $catalogDirectories),
            array_merge($communityConfig->getFixtureDirectories(), $fixtureDirectories),
            $featureFlags
        );
    }

    /**
     * @return string
     */
    private function getRootDirectory()
    {
        return __DIR__ .
            DIRECTORY_SEPARATOR .
            '..' .
            DIRECTORY_SEPARATOR .
            '..' .
            DIRECTORY_SEPARATOR .
            '..' .
            DIRECTORY_SEPARATOR .
            '..' .
            DIRECTORY_SEPARATOR .
            '..' .
            DIRECTORY_SEPARATOR;
    }
}
