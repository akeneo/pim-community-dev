<?php

namespace Akeneo\Test\Integration;

/**
 * Configuration of a TestCase.
 * Here is defined the catalog that has to be loaded and the directories of the fixtures.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Configuration
{
    /** @var array */
    protected $catalogDirectories;

    /** @var array */
    protected $fixtureDirectories;

    /**
     * @param array      $catalogDirectories       The catalog directories to load
     * @param array|null $fixtureDirectories       The fixtures directories. Will look at least in "test/fixtures/".
     *
     * @throws \Exception
     */
    public function __construct(array $catalogDirectories, array $fixtureDirectories = null) {
        $this->catalogDirectories = $catalogDirectories;
        foreach ($this->catalogDirectories as $catalogDirectory) {
            if (!is_dir($catalogDirectory)) {
                throw new \Exception(sprintf('The catalog directory "%s" does not exist.', $catalogDirectory));
            }
        }

        $this->fixtureDirectories = [];
        if (null !== $fixtureDirectories) {
            $this->fixtureDirectories = array_merge($this->fixtureDirectories, $fixtureDirectories);
        }

        $defaultFixturePath = self::getRootDirectory() . DIRECTORY_SEPARATOR . 'tests' .
            DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR;
        $this->fixtureDirectories[] = $defaultFixturePath;

        foreach ($this->fixtureDirectories as $fixtureDirectory) {
            if (!is_dir($fixtureDirectory)) {
                throw new \Exception(sprintf('The fixture directory "%s" does not exist.', $fixtureDirectory));
            }
        }
    }

    /**
     * @return array
     */
    public function getCatalogDirectories()
    {
        return $this->catalogDirectories;
    }

    /**
     * @return array
     */
    public function getFixtureDirectories()
    {
        return $this->fixtureDirectories;
    }

    /**
     * @return string
     */
    public static function getTechnicalSqlCatalogPath()
    {
        return realpath(self::getRootDirectory() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'catalog' .
            DIRECTORY_SEPARATOR . 'technical_sql');
    }

    /**
     * @return string
     */
    public static function getTechnicalCatalogPath()
    {
        return realpath(self::getRootDirectory() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'catalog' .
            DIRECTORY_SEPARATOR . 'technical');
    }

    /**
     * @return string
     */
    public static function getMinimalCatalogPath()
    {
        return realpath(self::getRootDirectory() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Pim' .
            DIRECTORY_SEPARATOR . 'Bundle' . DIRECTORY_SEPARATOR . 'InstallerBundle' . DIRECTORY_SEPARATOR .
            'Resources' . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'minimal');
    }

    /**
     * @return string
     */
    public static function getReferenceDataFixtures()
    {
        return realpath(self::getRootDirectory() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Acme' .
            DIRECTORY_SEPARATOR . 'Bundle' . DIRECTORY_SEPARATOR . 'AppBundle' . DIRECTORY_SEPARATOR . 'Resources' .
            DIRECTORY_SEPARATOR . 'fixtures');
    }

    /**
     * Returns the path to a given functional (aka behat) catalog.
     *
     * @param $catalog
     *
     * @return string
     */
    public static function getFunctionalCatalog($catalog)
    {
        return realpath(self::getRootDirectory(). DIRECTORY_SEPARATOR . 'features'. DIRECTORY_SEPARATOR . 'Context' .
            DIRECTORY_SEPARATOR .'catalog'. DIRECTORY_SEPARATOR . $catalog);
    }

    /**
     * Returns the path to a functional (aka behat) fixture folder.
     *
     * @return string
     */
    public static function getFunctionalFixtures()
    {
        return realpath(self::getRootDirectory(). DIRECTORY_SEPARATOR . 'features'. DIRECTORY_SEPARATOR . 'Context' .
            DIRECTORY_SEPARATOR .'fixtures');
    }

    /**
     * @return string
     */
    private static function getRootDirectory()
    {
        return realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
    }
}
