<?php

namespace Akeneo\Test\Integration;

/**
 * Configuration of a TestCase.
 * Here is defined the catalog that has to be loaded, the directories of the fixtures and tells if the database
 * should be purged between each test.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Configuration
{
    /** @var bool */
    protected $purgeDatabaseForEachTest;

    /** @var array */
    protected $catalogDirectories;

    /** @var array */
    protected $fixtureDirectories;

    /**
     * @param array      $catalogDirectories       The catalog directories to load
     * @param bool       $purgeDatabaseForEachTest If you don't need to purge database between each test in the
     *                                             same test class, set to false
     * @param array|null $fixtureDirectories       The fixtures directories. Will look at least in "test/fixtures/".
     *
     * @throws \Exception
     */
    public function __construct(
        array $catalogDirectories,
        $purgeDatabaseForEachTest = true,
        array $fixtureDirectories = null
    ) {
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
        $this->fixtureDirectories [] = $defaultFixturePath;

        foreach ($this->fixtureDirectories as $fixtureDirectory) {
            if (!is_dir($fixtureDirectory)) {
                throw new \Exception(sprintf('The fixture directory "%s" does not exist.', $fixtureDirectory));
            }
        }

        $this->purgeDatabaseForEachTest = $purgeDatabaseForEachTest;
    }

    /**
     * @return bool
     */
    public function isDatabasePurgedForEachTest()
    {
        return $this->purgeDatabaseForEachTest;
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
    private static function getRootDirectory()
    {
        return realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
    }
}
