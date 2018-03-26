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
     * @param array $catalogDirectories The catalog directories to load
     * @param array $fixtureDirectories The fixtures directories. Will look at least in "test/fixtures/".
     *
     * @throws \Exception
     */
    public function __construct(array $catalogDirectories, array $fixtureDirectories = [])
    {
        $this->catalogDirectories = $catalogDirectories;
        foreach ($this->catalogDirectories as $catalogDirectory) {
            if (!is_dir($catalogDirectory)) {
                throw new \Exception(sprintf('The catalog directory "%s" does not exist.', $catalogDirectory));
            }
        }

        $this->fixtureDirectories = $fixtureDirectories;
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
}
