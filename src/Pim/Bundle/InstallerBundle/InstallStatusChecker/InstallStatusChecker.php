<?php

declare(strict_types=1);

namespace Pim\Bundle\InstallerBundle\InstallStatusChecker;

use Symfony\Component\Yaml\Yaml;

/**
 * InstallStatusChecker : Check and persist a status file PIM has been installed and when.
 * As the location of the file containing the flag is configurable (install_status_dir), the flag cannot be load
 * directly in the container.
 * Parameter "install_status_dir" can be a relative path to the project dir or an absolute path (if beginning with '/').
 *
 *
 * @author    Vincent Berruchon <vincent.berruchon@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallStatusChecker
{
    public const INSTALL_STATUS_FILENAME_PREFIX = 'install';
    public const INSTALL_STATUS_FILENAME_EXT = '.yml';
    public const ATTR_INSTALL_STATUS_DIR = 'install_status_dir';
    public const ATTR_INSTALLED_STATUS_SECTION ='install';
    public const ATTR_INSTALLED_STATUS ='installed';

    /**
     * Path to the project directory
     * @var string $projectDir
     */
    protected $projectDir;

    /**
     * Absolute or relative path to the directory to the install flag file.
     * @var string $absoluteDirectory
     */
    protected $configStatusFileDir;

    /**
     * environment
     * @var string $env
     */
    protected $env;

    /**
     * @param string $projectDir Path to parameters storage directory
     * @param string string $statusFileDir
     * @param string $env
     */
    public function __construct(string $projectDir, string $statusFileDir, string $env)
    {
        if (empty($statusFileDir)) {
            throw new \RuntimeException('Please configure ' . self::ATTR_INSTALL_STATUS_DIR);
        }
        $this->projectDir = $projectDir;
        $this->configStatusFileDir = $statusFileDir;
        $this->env = $env;
    }

    public function getAbsoluteDirectoryPath() : string
    {
        if (!empty($this->configStatusFileDir) && mb_substr($this->configStatusFileDir, 0, 1) === '/') {
            return $this->configStatusFileDir;
        }
        return $this->projectDir . '/' . $this->configStatusFileDir;
    }

    public function getAbsoluteFilePath() : string
    {
        return $this->getAbsoluteDirectoryPath() . '/' . self::INSTALL_STATUS_FILENAME_PREFIX
            . $this->getEnvSuffixe($this->env)
            . self::INSTALL_STATUS_FILENAME_EXT;
    }

    /**
     * @return string return the string 'false' if not installed or return the timestamp of installation.
     */
    public function getInstalledFlag() : string
    {
        $absoluteFilePath=$this->getAbsoluteFilePath();

        // if PIM not installed, the file probably doesn't exist
        if (!file_exists($absoluteFilePath)) {
            return 'false';
        }
        $data = Yaml::parse(file_get_contents($absoluteFilePath));
        if (!is_array($data) || !isset($data[self::ATTR_INSTALLED_STATUS_SECTION])
         || !is_array($data[self::ATTR_INSTALLED_STATUS_SECTION])) {
            return 'false';
        }
        if (array_key_exists(self::ATTR_INSTALLED_STATUS, $data[self::ATTR_INSTALLED_STATUS_SECTION])) {
            $installed = $data[self::ATTR_INSTALLED_STATUS_SECTION][self::ATTR_INSTALLED_STATUS];
            if (empty($installed) || $installed==='false') {
                return 'false';
            } else {
                return $installed;
            }
        }
        return 'false';
    }

    /**
    * @return bool Return a boolean about installation state of the PIM
    */
    public function isInstalled() : bool
    {
        if ($this->getInstalledFlag()==='false') {
            return false;
        }
        return true;
    }

    /**
     * persistInstallStatus : write to file the installTime timestamp.
     *
     * @param string $installTime
     */
    public function persistInstallStatus(string $installTime) : void
    {
        $install = [];
        $install[self::ATTR_INSTALLED_STATUS] = $installTime;
        $absoluteFilePath=$this->getAbsoluteFilePath();

        if (false === file_put_contents(
            $absoluteFilePath,
            Yaml::dump([self::ATTR_INSTALLED_STATUS_SECTION => $install])
        )) {
            throw new \RuntimeException(
                sprintf(
                    'Failed to write to "%s". "%s" status not set!',
                    $absoluteFilePath,
                    self::ATTR_INSTALLED_STATUS
                )
            );
        }
    }

    /**
     * @param string $env
     * @return string If behat or test we add "_test" (will be finally "install_test.yml").
     *  For prod and test, add nothing.
     */
    protected function getEnvSuffixe(string $env) : string
    {
        if ($env==='behat' || $env==='test') {
            return '_test';
        }
        return '';
    }
}
