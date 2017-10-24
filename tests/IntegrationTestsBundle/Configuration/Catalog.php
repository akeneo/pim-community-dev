<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Configuration;

use Akeneo\Test\Integration\Configuration;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Catalog implements CatalogInterface
{
    /**
     * {@inheritdoc}
     */
    public function useTechnicalSqlCatalog(): Configuration
    {
        $catalogDirectories = [realpath($this->getRootDirectory() . 'tests' . DIRECTORY_SEPARATOR . 'catalog' .
            DIRECTORY_SEPARATOR . 'technical_sql')];

        $fixtureDirectories = [
            $this->getTechnicalFixtures(),
            $this->getReferenceDataFixtures()
        ];

        return new Configuration($catalogDirectories, $fixtureDirectories);
    }

    /**
     * {@inheritdoc}
     */
    public function useTechnicalCatalog(): Configuration
    {
        $catalogDirectories = [realpath($this->getRootDirectory() . 'tests' . DIRECTORY_SEPARATOR . 'catalog' .
            DIRECTORY_SEPARATOR . 'technical')];

        $fixtureDirectories = [
            $this->getTechnicalFixtures(),
            $this->getReferenceDataFixtures()
        ];

        return new Configuration($catalogDirectories, $fixtureDirectories);
    }

    /**
     * {@inheritdoc}
     */
    public function useMinimalCatalog(): Configuration
    {
        $catalogDirectories = [realpath($this->getRootDirectory() . 'src' . DIRECTORY_SEPARATOR . 'Pim' .
            DIRECTORY_SEPARATOR . 'Bundle' . DIRECTORY_SEPARATOR . 'InstallerBundle' . DIRECTORY_SEPARATOR .
            'Resources' . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'minimal')];

        $fixtureDirectories = [
            $this->getReferenceDataFixtures(),
            $this->getTechnicalFixtures(),
        ];

        return new Configuration($catalogDirectories, $fixtureDirectories);
    }

    /**
     * {@inheritdoc}
     */
    public function useFunctionalCatalog(string $catalog): Configuration
    {
        $catalogDirectories = [realpath($this->getRootDirectory() . 'features'. DIRECTORY_SEPARATOR . 'Context' .
            DIRECTORY_SEPARATOR .'catalog'. DIRECTORY_SEPARATOR . $catalog)];

        $fixtureDirectories = [
            realpath($this->getRootDirectory(). DIRECTORY_SEPARATOR . 'features'. DIRECTORY_SEPARATOR . 'Context' .
            DIRECTORY_SEPARATOR .'fixtures'),
            $this->getReferenceDataFixtures()
        ];

        return new Configuration($catalogDirectories, $fixtureDirectories);
    }


    /**
     * {@inheritdoc}
     */
    private function getReferenceDataFixtures(): string
    {
        $path = $this->getRootDirectory() . 'src' . DIRECTORY_SEPARATOR . 'Acme' .
            DIRECTORY_SEPARATOR . 'Bundle' . DIRECTORY_SEPARATOR . 'AppBundle' . DIRECTORY_SEPARATOR . 'Resources' .
            DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
        $test = realpath($path);

        return $test;
    }

    private function getTechnicalFixtures(): string
    {
        return realpath($this->getRootDirectory() . 'tests' . DIRECTORY_SEPARATOR . 'fixtures');
    }

    /**
     * @return string
     */
    private function getRootDirectory()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
    }
}
