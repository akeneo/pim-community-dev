<?php

namespace Pim\Bundle\InstallerBundle\FixtureLoader;

/**
 * Provides the path of the data fixtures
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FixturePathProvider
{
    /** @var array */
    protected $bundles;

    /** @var string */
    protected $installerData;

    /**
     * @param array  $bundles
     * @param string $installerData
     */
    public function __construct(array $bundles, $installerData)
    {
        $this->bundles = $bundles;
        $this->installerData = $installerData;
    }

    /**
     * Get the path of the data used by the installer
     *
     * @return string
     */
    public function getFixturesPath()
    {
        $installerDataDir = null;

        if (preg_match('/^(?P<bundle>\w+):(?P<directory>\w+)$/', $this->installerData, $matches)) {
            $reflection = new \ReflectionClass($this->bundles[$matches['bundle']]);
            $installerDataDir = dirname($reflection->getFilename()) . '/Resources/fixtures/' . $matches['directory'];
        } else {
            $installerDataDir = $this->installerData;
        }

        if (null === $installerDataDir || !is_dir($installerDataDir)) {
            throw new \RuntimeException('Installer data directory cannot be found.');
        }

        if (DIRECTORY_SEPARATOR !== substr($installerDataDir, -1, 1)) {
            $installerDataDir .= DIRECTORY_SEPARATOR;
        }

        return $installerDataDir;
    }
}
