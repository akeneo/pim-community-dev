<?php

namespace Akeneo\Test\Integration;

/**
 * Configuration of a TestCase.
 * Here is defined the catalog that has to be loaded and the directories of the fixtures, and the feature flags to activate before installation.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Configuration
{
    private array $catalogDirectories;
    private array $fixtureDirectories;
    private array $featureFlagsBeforeInstall;

    /**
     * @param array $catalogDirectories The catalog directories to load
     * @param array $fixtureDirectories The fixtures directories. Will look at least in "test/fixtures/".
     * @param array $featureFlagsBeforeInstall Feature flags to activate before installation, because it impacts the loading of the data, such as the permission
     *
     * @throws \Exception
     */
    public function __construct(array $catalogDirectories, array $fixtureDirectories = [], array $featureFlagsBeforeInstall = [])
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

        $this->featureFlagsBeforeInstall = $featureFlagsBeforeInstall;
    }

    public function getCatalogDirectories(): array
    {
        return $this->catalogDirectories;
    }

    public function getFixtureDirectories(): array
    {
        return $this->fixtureDirectories;
    }

    public function getFeatureFlagsBeforeInstall(): array {
        return $this->featureFlagsBeforeInstall;
    }
}
